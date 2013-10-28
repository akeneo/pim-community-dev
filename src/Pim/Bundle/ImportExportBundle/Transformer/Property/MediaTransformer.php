<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Media attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        throw new Exception('UNIMPLEMENTED');

        try {
            $file = new File($value);
        } catch (FileNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('File not found: %s', $value));
        }

        return array('file' => $file);
    }

}
