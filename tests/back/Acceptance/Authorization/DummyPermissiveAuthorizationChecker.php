<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\Authorization;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * On EE, product updates are delegated to some GrantedUpdater (see Akeneo\Pim\Permission\Component\Updater\GrantedProductUpdater).
 * This "Permissive" checker always return true to bypass those checks.
 * It's only used for Acceptance tests purpose.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class DummyPermissiveAuthorizationChecker implements AuthorizationCheckerInterface
{
    public function isGranted($attributes, $subject = null)
    {
        return true;
    }
}
