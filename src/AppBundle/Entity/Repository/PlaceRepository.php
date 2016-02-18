<?php
namespace AppBundle\Entity\Repository;

/**
 * Place repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class PlaceRepository extends EntryRepository
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
            ]
        );
    }
}
