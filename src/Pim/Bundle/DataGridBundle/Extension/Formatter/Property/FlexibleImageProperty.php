<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

/**
 * Flexible image field property, able to render image attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleImageProperty extends FlexibleTwigProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $backend = $value['attribute']['backendType'];
        $value   = $value[$backend];

        if ($fileName = $value['filename']) {
            return $this->getTemplate()->render(
                array(
                    'filename' => $fileName,
                    'title'    => $value['originalFilename']
                )
            );
        }

        return null;
    }
}
