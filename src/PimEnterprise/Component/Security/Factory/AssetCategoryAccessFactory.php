<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Factory;

use Akeneo\Component\Classification\Model\CategoryInterface;

/**
 * Asset Category Access Factory
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetCategoryAccessFactory
{
    /** @var string */
    protected $accessClass;

    /**
     * @param string $accessClass
     */
    public function __construct($accessClass)
    {
        $this->accessClass = $accessClass;
    }

    /**
     * @return CategoryInterface
     */
    public function create()
    {
        return new $this->accessClass();
    }
}
