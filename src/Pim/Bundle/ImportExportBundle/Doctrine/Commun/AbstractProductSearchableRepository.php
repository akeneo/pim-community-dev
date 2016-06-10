<?php

namespace Pim\Bundle\ImportExportBundle\Doctrine\Commun;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\Query as ORMQuery;
use Doctrine\ODM\MongoDB\Query\Query as ODMQuery;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Abstract product searchable repository
 * 
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductSearchableRepository implements SearchableRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $options['attribute'] = $this->attributeRepository->findOneBy(['attributeType' => AttributeTypes::IDENTIFIER]);
        
        $query = $this->buildQuery($productQueryBuilder, $search, $options);
        
        $result = array_reduce(
            $query->execute(),
            function($formattedProducts, ProductInterface $products) {
                $identifier = $products->getIdentifier()->getData();
                $formattedProducts[] = ['id' => $identifier, 'text' => $identifier];

                return $formattedProducts;
            },
            []
        );

        return $result;
    }

    /**
     * @param ProductQueryBuilderInterface $productQueryBuilder
     * @param string                       $search
     * @param array                        $options
     * 
     * @return ORMQuery|ODMQuery
     */
    abstract protected function buildQuery(
        ProductQueryBuilderInterface $productQueryBuilder,
        $search,
        array $options
    );
}
