<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Resolve entities using completeness on save
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessSavingOptionsResolver
{
    /**
     * Resolve options for a single product save
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush'    => true,
                'schedule' => true
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Resolve options for a bulk products save
     *
     * @param array $options
     *
     * @return array
     */
    public function resolveSaveAllOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush'    => true,
                'schedule' => true,
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
        $resolver->setOptional(['flush', 'schedule']);
        $resolver->setAllowedTypes(
            [
                'flush'    => 'bool',
                'schedule' => 'bool'
            ]
        );

        return $resolver;
    }
}
