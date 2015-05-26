<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Resolve product saving options for single or bulk save
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSavingOptionsResolver implements SavingOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush'             => true,
                'recalculate'       => true,
                'schedule'          => true,
                'flush_only_object' => false
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
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush'             => true,
                'recalculate'       => false,
                'schedule'          => true,
                'flush_only_object' => false,
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['flush', 'recalculate', 'schedule', 'flush_only_object']);
        $resolver->setAllowedTypes(
            [
                'flush'             => 'bool',
                'recalculate'       => 'bool',
                'schedule'          => 'bool',
                'flush_only_object' => 'bool'
            ]
        );

        return $resolver;
    }
}
