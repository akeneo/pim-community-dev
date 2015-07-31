<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve saving options for single or bulk save
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSavingOptionsResolver implements SavingOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSaveAllOptions(array $options)
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
