<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;

/**
 * Be able to use the native MySQL 5.7 JSON type in our current Doctrine DBAL version.
 *
 * TODO: once https://github.com/doctrine/dbal/pull/2653 will be merged, we'll be able to drop this class
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonType extends JsonArrayType
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'json';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value === null ? null : parent::convertToPHPValue($value, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'native_json';
    }
}
