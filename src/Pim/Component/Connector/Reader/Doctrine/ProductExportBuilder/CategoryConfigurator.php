<?php

namespace Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configure the option resolver for the categories filter.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryConfigurator implements FilterConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver, array $context = [])
    {
        $resolver
            ->setRequired('categories')
            ->setAllowedTypes('categories', ['array'])
            ->setNormalizer('categories', function (Options $options, $value) use ($context) {
                return [
                    'field'    => 'categories.id',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => $value,
                    'context'  => $context
                ];
            })
        ;
    }
}
