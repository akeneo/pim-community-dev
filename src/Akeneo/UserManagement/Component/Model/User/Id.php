<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Model\User;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Id
{
    /** @var int|null */
    private $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Only used by Doctrine to store user entities in the UoW entity map.
     */
    public function __toString()
    {
        return (string) $this->id;
    }
}
