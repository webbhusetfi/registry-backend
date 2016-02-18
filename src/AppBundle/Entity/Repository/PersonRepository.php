<?php
namespace AppBundle\Entity\Repository;

/**
 * Person repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class PersonRepository extends EntryRepository
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
                'gender' => $attributes['gender'],
                'firstName' => $attributes['firstName'],
                'lastName' => $attributes['lastName'],
                'birthYear' => $attributes['birthYear'],
                'birthMonth' => $attributes['birthMonth'],
                'birthDay' => $attributes['birthDay'],
            ]
        );
    }
}
