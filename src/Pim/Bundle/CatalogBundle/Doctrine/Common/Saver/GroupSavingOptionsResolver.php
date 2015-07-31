<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve group saving options
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSavingOptionsResolver implements SavingOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
                'add_products'            => [],
                'remove_products'         => [],
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSaveAllOptions(array $options)
    {
        return $this->resolveSaveOptions($options);
    }

    /**
     * @return OptionsResolver
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['flush', 'copy_values_to_products', 'add_products', 'remove_products'])
            ->setAllowedTypes('flush', 'bool')
            ->setAllowedTypes('copy_values_to_products', 'bool')
            ->setAllowedTypes('add_products', 'array')
            ->setAllowedTypes('remove_products', 'array');

        return $resolver;
    }
}
