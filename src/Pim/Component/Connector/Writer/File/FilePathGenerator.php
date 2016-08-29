<?php

namespace Pim\Component\Connector\Writer\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a simple placeholder replacement.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilePathGenerator implements FileExporterPathGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate($value, array $options = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve($options);

        return strtr($value, $options['parameters']);
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
