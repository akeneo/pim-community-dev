<?php

namespace Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configure the option resolver for the completeness filter.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessConfigurator implements FilterConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver, array $context = [])
    {
        $resolver
            ->setRequired('completeness')
            ->setAllowedTypes('completeness', 'string')
            ->setAllowedValues('completeness', ['at_least_one_selected_locale'])
            // to be compatible with former/custom product exports
            ->setDefault('completeness', 'at_least_one_selected_locale')
            ->setNormalizer('completeness', function (Options $options, $value) use ($context) {
                switch ($value) {
                    case 'at_least_one_selected_locale':
                        $value = [
                            'field'    => 'completeness',
                            'operator' => Operators::EQUALS,
                            'value'    => 100,
                            'context'  => $context
                        ];
                        break;
                    default:
                        throw new \Exception('not implemented yet, see story PIM-5426');
                }

                return $value;
            })
        ;
    }
}
