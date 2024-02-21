<?php

namespace Akeneo\Pim\Structure\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * Creates and configures a GroupeType instance.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeFactory implements SimpleFactoryInterface
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
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->groupTypeClass();
    }
}
