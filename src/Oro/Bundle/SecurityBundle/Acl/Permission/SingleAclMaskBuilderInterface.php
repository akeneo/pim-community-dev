<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Acl\Permission;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SingleAclMaskBuilderInterface
{
    /**
     * Returns the object entity concerned by the mask.
     *
     * @return ObjectIdentity
     */
    public function getOid(): ObjectIdentity;

    /**
     * Returns the default mask.
     *
     * @return int
     */
    public function getDefaultMask(): int;

    /**
     * Returns the default granting.
     *
     * @return bool
     */
    public function getDefaultGranting(): bool;
}
