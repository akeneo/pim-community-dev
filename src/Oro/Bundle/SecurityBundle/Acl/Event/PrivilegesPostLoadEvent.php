<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Acl\Event;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrivilegesPostLoadEvent
{
    private ArrayCollection $privileges;

    public function __construct(ArrayCollection $privileges)
    {
        $this->privileges = $privileges;
    }

    public function getPrivileges(): ArrayCollection
    {
        return $this->privileges;
    }

    public function setPrivileges(ArrayCollection $privileges): void
    {
        $this->privileges = $privileges;
    }
}
