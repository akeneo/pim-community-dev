<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\GroupTypeInterface;

/**
 * Class GroupTypeFactory
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeFactory
{
    /** @var string */
    protected $groupTypeClass;

    /**
     * @param string $groupTypeClass
     */
    public function __construct($groupTypeClass)
    {
        $this->groupTypeClass = $groupTypeClass;
    }

    /**
     * @return GroupTypeInterface
     */
    public function create()
    {
        return new $this->groupTypeClass();
    }
}
