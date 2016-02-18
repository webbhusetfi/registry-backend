<?php
namespace AppBundle\Entity\Repository;

/**
 * Organization repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class OrganizationRepository extends EntryRepository
{
    /**
     * Serialize attributes
     *
     * @param array $attributes Input attributes
     * @return array Output attributes
     */
    public function serialize(array $attributes)
    {
        return array_merge(
            parent::serialize($attributes),
            [
                'name' => $attributes['name'],
                'bank' => $attributes['bank'],
                'account' => $attributes['account'],
                'vat' => $attributes['vat'],
            ]
        );
    }
}
