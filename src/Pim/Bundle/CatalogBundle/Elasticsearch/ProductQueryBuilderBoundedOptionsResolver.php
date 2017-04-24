<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Pim\Component\Catalog\Query\ProductQueryBuilderOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve product query builder options for elasticsearch
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderBoundedOptionsResolver implements ProductQueryBuilderOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolver
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['locale', 'scope', 'limit', 'search_after', 'search_after_unique_key']);
        $resolver->setRequired(['locale', 'scope', 'limit']);

        return $resolver;
    }
}
