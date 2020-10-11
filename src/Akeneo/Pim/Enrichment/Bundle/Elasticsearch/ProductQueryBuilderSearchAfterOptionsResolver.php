<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve product query builder options for elasticsearch
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderSearchAfterOptionsResolver implements ProductQueryBuilderOptionsResolverInterface
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
        $resolver->setDefined(['locale', 'scope', 'limit', 'search_after', 'search_after_unique_key', 'with_document_type_facet']);
        $resolver->setRequired(['locale', 'scope', 'limit']);

        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setAllowedTypes('scope', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('search_after_unique_key', ['string', 'null']);
        $resolver->setAllowedTypes('with_document_type_facet', ['bool', 'null']);

        return $resolver;
    }
}
