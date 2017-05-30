<?php
namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\Entry;
use AppBundle\Entity\MailJob;
use AppBundle\Entity\Common\Interfaces\OrganizationInterface;

use AppBundle\Service\Common\DoctrineService;
use JSend\JSendResponse;


class MailService extends DoctrineService
{
    private $curl;

    public function __destruct()
    {
        if (isset($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function getMethods()
    {
        return ['create', 'read', 'search'];
    }


    public function fetchMailJob(array $filter, $method = 'read')
    {
        if (!isset($filter['id'])) {
            return null;
        }
        $qb = $this->buildQuery($filter);
        $entities = $qb->getQuery()->getResult();
        if (count($entities) !== 1) {
            return null;
        }
        return $entities[0];
    }

    protected function getEntry(array $request)
    {
        if (!$this->getUser()->hasRole(User::ROLE_ADMIN)) {
            return $this->getUser()->getEntry();
        } else {
            $query = [
                'id' => (int)$request['entry']
            ];
            if (!$this->getUser()->hasRole(User::ROLE_SUPER_ADMIN)) {
                $query['registry'] = $this->getUser()->getRegistryId();
            }
            return $this->getRepository('AppBundle:Entry')->findOneBy($query);
        }
    }

    protected function getPrimaryAddress(Entry $entry)
    {
        $query = [
            'class' => 'PRIMARY',
            'entry' => $entry
        ];
        $addresses = $this->getRepository('AppBundle:Address')->findBy($query);
        if (count($addresses) != 1) {
            return null;
        }
        return $addresses[0];
    }

    protected function getRecipients(array $request)
    {
        $include = $filter = $order = [];
        $offset = $limit = null;

        if (isset($request['filter']) && is_array($request['filter'])) {
            $filter = $request['filter'];
        }
        if (isset($request['order']) && is_array($request['order'])) {
            $order = $request['order'];
        }
        if (isset($request['offset'])) {
            $offset = (int)$request['offset'];
        }
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }

        $qb = $this->get('registryapi.entry')->buildQuery(
            ['primaryAddress'],
            $filter,
            $order,
            $offset,
            $limit
        );
        $qb->select('entry.firstName', 'entry.lastName', 'primaryAddress.email');
        $qb->andWhere('primaryAddress.email IS NOT NULL');
        $qb->groupBy('primaryAddress.email');
        $statement = $qb->execute();

        $recipients = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $recipients[] = "{$row['firstName']} {$row['lastName']}<{$row['email']}>";
        }

        return $recipients;
    }

    protected function buildQuery(
        array $filter,
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $qb = $this->getManager()->createQueryBuilder()
            ->from('AppBundle:MailJob', 'mailJob')
            ->select('mailJob')
            ->innerJoin('mailJob.entry', 'entry');

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['registry'] = $user->getRegistryId();
        }
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $filter['entry'] = $user->getEntryId();
        }

        $repo = $this->getRepository('AppBundle:MailJob');

        $repo->applyWhereFilter($qb, 'mailJob', $filter);
        if (isset($filter['registry'])) {
            $qb
            ->andWhere($qb->expr()->eq('entry.registry', ':registry'))
            ->setParameter('registry', $filter['registry']);
        }
        if (isset($orderBy)) {
            $repo->applyOrderBy($qb, 'mailJob', $orderBy);
        }
        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }



    protected function apiCall(array $options, &$error)
    {
        if (!isset($this->curl)) {
            $this->curl = curl_init();
        } else {
            curl_reset($this->curl);
        }

        curl_setopt_array($this->curl, $options);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($this->curl);
        if ($result === false)  {
            $error = "Unable to connect to service provider.";
        } else {
            $response = json_decode($result, true);
            if ($response['success']) {
                return $response['data'];
            }
            $error = $response['error'];
        }
        return null;
    }

    protected function emailSend(
        array $sender,
        array $recipients,
        $subject,
        $message,
        &$error
    ) {
        $data = [];
        $data['apikey'] = $this->getParameter('elastic_apikey');
        if (isset($sender['email'])) {
            $data['from'] = $sender['email'];
        } else {
            $data['from'] = $this->getParameter('elastic_sender_email');
        }
        if (isset($sender['name'])) {
            $data['fromName'] = $sender['name'];
        } else {
            $data['fromName'] = $this->getParameter('elastic_sender_name');
        }
        if (isset($sender['replyToEmail'])) {
            $data['replyTo'] = $sender['replyToEmail'];
        }
        if (isset($sender['replyToName'])) {
            $data['replyToName'] = $sender['replyToName'];
        }
        $data['to'] = implode(',', $recipients);
        $data['subject'] = $subject;
        $data['bodyText'] = $message;
        $data['bodyHtml'] = str_replace("\n", '<br>', $message);

        $options = [
            CURLOPT_URL => 'https://api.elasticemail.com/v2/email/send',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ];

        return $this->apiCall($options, $error);
    }

    protected function emailGetStatus($transactionId, &$error)
    {
        $data = [];
        $data['apikey'] = $this->getParameter('elastic_apikey');
        $data['transactionID'] = $transactionId;
        $data['showAbuse'] = 'true';
        $data['showClicked'] = 'true';
        $data['showDelivered'] = 'true';
        $data['showErrors'] = 'true';
        $data['showFailed'] = 'true';
        //$data['showMessageIDs'] = 'true';
        $data['showOpened'] = 'true';
        $data['showPending'] = 'true';
        $data['showUnsubscribed'] = 'true';

        $query = http_build_query($data);
        $options = [
            CURLOPT_URL => "https://api.elasticemail.com/v2/email/getstatus?{$query}",
        ];

        return $this->apiCall($options, $error);
    }

    protected function emailStatus($messageId, &$error)
    {
        $data = [];
        $data['apikey'] = $this->getParameter('elastic_apikey');
        $data['messageID'] = $messageId;

        $query = http_build_query($data);
        $options = [
            CURLOPT_URL => "https://api.elasticemail.com/v2/email/status?{$query}",
        ];

        return $this->apiCall($options, $error);
    }

    protected function emailView($messageId, &$error)
    {
        $data = [];
        $data['apikey'] = $this->getParameter('elastic_apikey');
        $data['messageID'] = $messageId;

        $query = http_build_query($data);
        $options = [
            CURLOPT_URL => "https://api.elasticemail.com/v2/email/view?{$query}",
        ];

        return $this->apiCall($options, $error);
    }



    public function search(array $request)
    {
        $result = ['items' => [], 'foundCount' => 0];

        $filter = $order = [];
        $offset = $limit = null;

        if (isset($request['filter']) && is_array($request['filter'])) {
            $filter = $request['filter'];
        }
        if (isset($request['order']) && is_array($request['order'])) {
            $order = $request['order'];
        }
        if (isset($request['offset'])) {
            $offset = (int)$request['offset'];
        }
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }

        $qb = $this->buildQuery($filter, $order, $offset, $limit);
        $entities = $qb->getQuery()->getResult();

        if (count($entities)) {
            $repo = $this->getRepository('AppBundle:MailJob');
            foreach ($entities as $entity) {
                $result['items'][] = $repo->serialize($entity);
            }
            if (isset($offset) || isset($limit)) {
                $result['foundCount'] = $repo->getFoundCount($qb);
            } else {
                $result['foundCount'] = count($result['items']);
            }
        }

        return JSendResponse::success($result)->asArray();
    }

    public function create(array $request)
    {
        $messages = [];
        if (empty($request['subject'])) {
            $messages['subject'] = 'Required attribute';
        }
        if (empty($request['message'])) {
            $messages['message'] = 'Required attribute';
        }

        $entry = null;
        if (empty($request['entry'])) {
            $messages['entry'] = 'Required attribute';
        } else {
            $entry = $this->getEntry($request);
            if (!isset($entry)) {
                $messages['entry'] = 'Invalid entry';
            }
        }

        $recipients = $this->getRecipients($request);
        if (empty($recipients)) {
            $messages['error'] = 'No matching recipients';
        }

        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $sender = [];
        if (!empty($request['senderName'])) {
            $sender['name'] = $request['senderName'];
        } elseif ($entry instanceof OrganizationInterface) {
            $sender['name'] = $entry->getName();
        }
        $address = $this->getPrimaryAddress($entry);
        if (!empty($request['senderEmail'])
            && ($email = filter_var($request['senderEmail'], FILTER_VALIDATE_EMAIL))) {
            $sender['email'] = $email;
        } elseif ($address && ($email = $address->getEmail())) {
            $sender['email'] = $email;
        }

        $response = $this->emailSend(
            $sender,
            $recipients,
            $request['subject'],
            $request['message'],
            $error
        );

        if (!isset($response))  {
            $messages = ['error' => $error];
            return JSendResponse::fail($messages)->asArray();
        } else {
            $job = new MailJob();
            $job->setEntry($entry);
            $job->setTransactionId($response['transactionid']);
            $job->setMessageId($response['messageid']);

            $em = $this->getManager();
            $em->persist($job);
            $em->flush();

            $item = $this->getRepository('AppBundle:MailJob')->serialize($job);
            return JSendResponse::success(['item' => $item])->asArray();
        }
    }

    public function read(array $request)
    {
        $job = $this->fetchMailJob($request, __FUNCTION__);

        if (!isset($job)) {
            $messages = ['error' => 'Not found'];
            return JSendResponse::fail($messages)->asArray();
        }

        $item = $this->getRepository('AppBundle:MailJob')->serialize($job);
        $response = $this->emailGetStatus(
            $job->getTransactionId(),
            $error
        );
        if ($response) {
            $item['job'] = $response;
            unset($item['job']['messageids']);
        } else {
            $item['job'] = $error;
        }

        return JSendResponse::success(['item' => $item])->asArray();
    }
}
