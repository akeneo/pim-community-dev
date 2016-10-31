<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductRepository implements ProductRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    public function __construct(ProductQueryBuilderFactoryInterface $productQueryBuilderFactory)
    {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findByProject(ProjectInterface $project)
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();

        if (null === $productFilers = $project->getProductFilters()) {
            throw new \LogicException(sprintf('The project "%s" does not have product filters', $project->getLabel()));
        }

        foreach ($productFilers as $productFiler) {
            $productQueryBuilder->addFilter($productFiler['field'], $productFiler['operator'], $productFiler['value']);
        }

        return $productQueryBuilder->execute();
    }
}
