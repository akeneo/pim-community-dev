<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve removing options for single or bulk remove
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseRemovingOptionsResolver implements RemovingOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveRemoveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined(['flush_only_object'])
            ->setAllowedTypes('flush_only_object', 'bool')
            ->setDefaults(['flush_only_object' => false]);

        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveRemoveAllOptions(array $options)
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
        $resolver->setDefined(['flush'])
            ->setAllowedTypes('flush', 'bool')
            ->setDefaults(['flush' => true]);

        return $resolver;
    }
}
