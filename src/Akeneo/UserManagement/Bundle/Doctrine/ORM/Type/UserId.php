<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Doctrine\ORM\Type;

use Akeneo\UserManagement\Component\Model\User\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserId extends Type
{
    private const NAME = 'user_id';

    public function convertToDatabaseValue($id, AbstractPlatform $platform)
    {
        if ($id instanceof Id) {
            return $id->getId();
        }

        throw ConversionException::conversionFailed($id, 'integer');
    }

    public function convertToPHPValue($id, AbstractPlatform $platform)
    {
        try {
            return new Id((int) $id);
        } catch (\InvalidArgumentException $exception) {
            throw ConversionException::conversionFailed($id, Id::class);
        }
    }

    public function getName()
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }
}
