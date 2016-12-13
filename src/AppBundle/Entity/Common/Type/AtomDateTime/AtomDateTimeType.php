<?php

namespace AppBundle\Entity\Common\Type\AtomDateTime;

use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AtomDateTimeType extends DateTimeType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $dateTime = parent::convertToPHPValue($value, $platform);

        if (!$dateTime) {
            return $dateTime;
        }

        return new AtomDateTime($dateTime->format('Y-m-d H:i:s e'));
    }

    public function getName()
    {
        return 'atomdatetime';
    }
}
