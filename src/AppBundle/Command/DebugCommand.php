<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('registry:debug')
            ->setDescription('Debug command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get("doctrine");
        $repo = $doctrine->getRepository("AppBundle:Entry");
        $entry = $repo->find(1);
        $output->writeln("serialized:" . json_encode($entry->toArray()));
    }
}
