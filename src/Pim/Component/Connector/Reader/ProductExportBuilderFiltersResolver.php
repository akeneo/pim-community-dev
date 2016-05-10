<?php


namespace Pim\Component\Connector\Reader;

use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Resolve Product Export Builder options (@see \Pim\Component\Connector\Reader\ProductExportBuilderOptionsResolver)
 * to turn them into Product Query Builder options.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportBuilderFiltersResolver
{
    /**
     * @param array $options
     *
     * @return array
     */
    public function resolve(array $options)
    {
        $filters = [];

        foreach ($options as $field => $value) {
            if (null !== $value) {
                // at the moment it works as we have only the "enabled" filter in the options
                // we'll surely have to do something better with the coming Product Export Builder stories
                $filters[] = [
                    'field'    => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value'    => $value,
                    'context'  => []
                ];
            }
        }

        return $filters;
    }
}
