<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

/**
 * Generate the path of medias to export.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaExporterPathGenerator implements FileExporterPathGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate($value, array $options = [])
    {
        $identifier = str_replace(DIRECTORY_SEPARATOR, '_', $options['identifier']);
        $target = sprintf('files/%s/%s', $identifier, $options['code']);

        if (null !== $value['locale']) {
            $target .= DIRECTORY_SEPARATOR . $value['locale'];
        }
        if (null !== $value['scope']) {
            $target .= DIRECTORY_SEPARATOR . $value['scope'];
        }

        return $target . DIRECTORY_SEPARATOR;
    }
}
