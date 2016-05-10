<?php


namespace Pim\Component\Connector\Reader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resolve UI options of the Product Export Builder to turn them into "Product Query Builder  ready" options.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportBuilderOptionsResolver
{
    /**
     * $uiOptions = [
     *     'enabled' => 'enabled', // or 'disabled' or 'all'
     * ]
     *
     *
     * @param array $uiOptions
     *
     * @return array
     */
    public function resolve(array $uiOptions)
    {
        $resolver = new OptionsResolver();

        $this->prepareProductStatus($resolver);

        return $resolver->resolve($uiOptions);
    }

    /**
     * Prepare the options resolver for the product status ("enabled") option.
     *
     * @param OptionsResolver $resolver
     */
    private function prepareProductStatus(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('enabled', true) // enabled by default to be compatible with former/custom exports
            ->setAllowedTypes('enabled', 'bool')
            ->setNormalizer('enabled', function (Options $options, $value) {
                switch ($value) {
                    case 'enabled':
                        $value = true;
                        break;
                    case 'disabled':
                        $value = false;
                        break;
                    default:
                        $value = null;
                }

                return $value;
            })
        ;
    }
}
