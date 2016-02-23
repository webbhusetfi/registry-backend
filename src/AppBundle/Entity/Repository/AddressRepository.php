<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;

/**
 * Address repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class AddressRepository extends Repository
{
    /**
     * Serialize attributes
     *
     * @param array $attributes Input attributes
     * @return array Output attributes
     */
    public function serialize(array $attributes)
    {
        return [
            'id' => $attributes['id'],
            'entry' => $attributes['entry_id'],
            'name' => $attributes['name'],
            'street' => $attributes['street'],
            'postalCode' => $attributes['postalCode'],
            'town' => $attributes['town'],
            'country' => $attributes['country'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'mobile' => $attributes['mobile'],
        ];
    }
}
