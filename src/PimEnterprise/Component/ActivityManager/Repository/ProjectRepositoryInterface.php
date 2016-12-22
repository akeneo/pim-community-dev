<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectRepositoryInterface extends
    ObjectRepository ,
    IdentifiableObjectRepositoryInterface,
    SearchableRepositoryInterface
{
    /**
     * Add a product to a project.
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product);
}
