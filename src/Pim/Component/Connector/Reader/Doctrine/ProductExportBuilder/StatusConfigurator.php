<?php

namespace Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configure the option resolver for the product status filter.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StatusConfigurator implements FilterConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver, array $context = [])
    {
        $resolver
            ->setRequired('enabled')
            ->setAllowedTypes('enabled', 'string')
            ->setAllowedValues('enabled', ['enabled', 'disabled', 'all'])
            ->setDefault('enabled', true) // to be compatible with former/custom product exports
            ->setNormalizer('enabled', function (Options $options, $value) use ($context) {
                switch ($value) {
                    case 'enabled':
                        $value = [
                            'field'    => 'enabled',
                            'operator' => Operators::EQUALS,
                            'value'    => true,
                            'context'  => $context
                        ];
                        break;
                    case 'disabled':
                        $value = [
                            'field'    => 'enabled',
                            'operator' => Operators::EQUALS,
                            'value'    => false,
                            'context'  => $context
                        ];
                        break;
                    default:
                        $value = null;
                }

                return $value;
            })
        ;
    }
}
