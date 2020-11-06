<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
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
    public function resolveSaveOptions(array $options): array
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'copy_values_to_products' => false,
                'add_products'            => [],
                'remove_products'         => [],
                'unitary'                 => true,
            ]
        );

        return $resolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSaveAllOptions(array $options): array
    {
        return array_merge($this->resolveSaveOptions($options), ['unitary' => false]);
    }

    protected function createOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['copy_values_to_products', 'add_products', 'remove_products'])
            ->setAllowedTypes('copy_values_to_products', 'bool')
            ->setAllowedTypes('add_products', 'array')
            ->setAllowedTypes('remove_products', 'array');

        return $resolver;
    }
}
