<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

/**
 * Image field property, able to render image attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageProperty extends TwigProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $fileName = urlencode($value['data']['filePath']);
        $title = $value['data']['originalFilename'];

        if (!empty($fileName)) {
            return $this->getTemplate()->render(
                [
                    'filename' => $fileName,
                    'title'    => $title
                ]
            );
        }

        return null;
    }
}
