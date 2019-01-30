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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait CheckAccessTrait
{
    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param string $permissionName
     *
     * @throws AccessDeniedException
     */
    private function checkAccess(string $permissionName): void
    {
        if (true !== $this->securityFacade->isGranted($permissionName)) {
            throw new AccessDeniedException();
        }
    }
}
