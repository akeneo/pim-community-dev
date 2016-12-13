<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Repository;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectRepositoryInterface
{
    /**
     * Add a product to a project.
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product);
}
