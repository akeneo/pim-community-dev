<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Aims to wrap the creation configuration of the product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryFactory implements ProductQueryFactoryInterface
{
    /** @var ProductRepositoryInterface */
    protected $repository;

    /** @var ProductQueryBuilderInterface */
    protected $productQueryBuilder;

    /** @var CatalogContext */
    protected $context;

    /**
     * @param ProductRepositoryInterface   $repository
     * @param ProductQueryBuilderInterface $productQueryBuilder
     * @param CatalogContext               $context
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        ProductQueryBuilderInterface $productQueryBuilder,
        CatalogContext $context
    ) {
        $this->repository = $repository;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        // TODO locale and scope check with option resolver ?
        $this->context->setLocaleCode($options['locale_code']);
        $this->context->setScopeCode($options['scope_code']);

        $qb = $this->repository->createQueryBuilder('p');
        $this->productQueryBuilder->setQueryBuilder($qb);

        return $this->productQueryBuilder;
    }
}
