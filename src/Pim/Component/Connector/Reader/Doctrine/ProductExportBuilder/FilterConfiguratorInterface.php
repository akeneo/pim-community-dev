<?php

namespace Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configure an option resolver to be able to turn Product Export Builder options
 * into Product Query Builder filters.
 *
 * In the implementations, for each export builder option, you should:
 *     - set if required or optional
 *     - set the allowed types
 *     - set the allowed values
 *     - set the default value (if needed)
 *     - normalize the option to a Product Query Builder filter with $resolver->setNormalizer().
 *       If you don't need to add a PQB filter regarding your option value, then
 *       normalize it to null (@see \Pim\Component\Connector\Reader\Doctrine\ProductExportBuilder\StatusConfigurator).
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterConfiguratorInterface
{
    /**
     * @param OptionsResolver $resolver
     * @param array           $context
     */
    public function configure(OptionsResolver $resolver, array $context = []);
}
