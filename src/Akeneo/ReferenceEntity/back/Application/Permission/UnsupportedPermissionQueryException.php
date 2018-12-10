<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Permission;

/**
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UnsupportedPermissionQueryException extends \LogicException
{
    public static function create(PermissionCheckQuery $permissionQuery): self
    {
        return new self(
            sprintf('Unsupported permission check of type "%s"', get_class($permissionQuery))
        );
    }
}
