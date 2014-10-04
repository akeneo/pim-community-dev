<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QuerySorterRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Aims to wrap the creation configuration of the product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryFactory implements ProductQueryFactoryInterface
{
    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $productClass;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** QueryFilterRegistryInterface */
    protected $filterRegistry;

    /** QuerySorterRegistryInterface */
    protected $sorterRegistry;

    /**
     * @param ObjectManager                $om
     * @param string                       $productClass
     * @param AttributeRepository          $attributeRepository
     * @param QueryFilterRegistryInterface $filterRegistry
     * @param QuerySorterRegistryInterface $sorterRegistry
     */
    public function __construct(
        ObjectManager $om,
        $productClass,
        AttributeRepository $attributeRepository,
        QueryFilterRegistryInterface $filterRegistry,
        QuerySorterRegistryInterface $sorterRegistry
    ) {
        $this->om = $om;
        $this->productClass = $productClass;
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        // TODO : the PQB class as class parameter
        $pqb = new ProductQueryBuilder(
            $this->attributeRepository,
            $this->filterRegistry,
            $this->sorterRegistry
        );

        $repository = $this->om->getRepository($this->productClass);
        $method = $options['repository_method'];
        if ($options['repository_parameters'] !== null) {
            $qb = $repository->$method($options['repository_parameters']);
        } else {
            $qb = $repository->$method();
        }
        $pqb->setQueryBuilder($qb);

        return $pqb;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        // TODO locale and scope by default ? check with option resolver ?
        // $this->context->setLocaleCode($options['locale_code']);
        // $this->context->setScopeCode($options['scope_code']);
        $resolver->setOptional(['repository_method', 'repository_parameters']);
        $resolver->setDefaults(
            [
                'repository_method' => 'createQueryBuilder',
                'repository_parameters' => null,
            ]
        );
    }
}
