<?php

namespace Pim\Component\Connector\Writer\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Implementation of FilePathResolverInterface that provides a simple placeholder replacement.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilePathResolver implements FilePathResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve($filePath, $options = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        return strtr($filePath, $options['parameters']);
    }

    /**
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired('parameters');
        $optionsResolver->setAllowedTypes('parameters', 'array');
    }
}
