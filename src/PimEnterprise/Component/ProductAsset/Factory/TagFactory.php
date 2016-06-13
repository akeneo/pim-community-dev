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

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;

/**
 * Tag factory
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class TagFactory implements SimpleFactoryInterface
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
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->tagClass();
    }

    /**
     * Create a new empty Tag
     *
     * @return TagInterface
     *
     * @deprecated Will be removed in 1.7. Use create() instead.
     */
    public function createTag()
    {
        return $this->create();
    }
}
