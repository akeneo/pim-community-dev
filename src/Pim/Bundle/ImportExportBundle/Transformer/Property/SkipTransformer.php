<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

/**
 * Skip transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
    }
}
