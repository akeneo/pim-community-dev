<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Repository\NativeSql;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectRepository implements ProjectRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $objectManager;

    /**
     * @param EntityManagerInterface $objectManager
     */
    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function addProduct(ProjectInterface $project, ProductInterface $product)
    {
        $this->objectManager->getConnection()->insert('akeneo_activity_manager_project_product', [
            'project_id' => $project->getId(),
            'product_id' => $product->getId(),
        ]);
    }
}
