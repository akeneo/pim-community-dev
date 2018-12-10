<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Permission;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class PermissionCheckQuery
{
    /** @var string $userIdentifier */
    public $userIdentifier;
}
