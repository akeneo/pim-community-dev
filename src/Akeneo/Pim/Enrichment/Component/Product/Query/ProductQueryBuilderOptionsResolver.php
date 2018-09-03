<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve product query builder options
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderOptionsResolver implements ProductQueryBuilderOptionsResolverInterface
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
        $resolver->setRequired(['locale', 'scope']);

        return $resolver;
    }
}
