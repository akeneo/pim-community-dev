<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SecurityFacadeStub extends SecurityFacade
{
    private array $permissions = [];

    public function isGranted($acl, $object = null)
    {
        return $this->permissions[$acl];
    }

    public function setIsGranted(string $acl, bool $isGranted)
    {
        $this->permissions[$acl] = $isGranted;
    }
}
