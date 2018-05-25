<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Asset\Component\Model\TagInterface;

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
}
