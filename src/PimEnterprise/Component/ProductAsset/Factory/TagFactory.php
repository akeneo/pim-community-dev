<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Factory;

use PimEnterprise\Component\ProductAsset\Model\TagInterface;

/**
 * Tag factory
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class TagFactory
{
    /** @var string */
    protected $tagClass;

    /**
     * @param string $tagClass
     */
    public function __construct($tagClass)
    {
        $this->tagClass = $tagClass;
    }

    /**
     * Create a new empty Tag
     *
     * @return TagInterface
     */
    public function createTag()
    {
        return new $this->tagClass();
    }
}
