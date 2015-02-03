<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Resolve saving options for single or bulk save
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSavingOptionsResolver
{
    /**
     * Resolve options for a single save
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setOptional(['flush_only_object']);
        $resolver->setAllowedTypes(['flush_only_object' => 'bool']);
        $resolver->setDefaults(['flush_only_object' => false]);
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Resolve options for a bulk save
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveSaveAllOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['flush']);
        $resolver->setAllowedTypes(['flush' => 'bool']);
        $resolver->setDefaults(['flush' => true]);

        return $resolver;
    }
}
