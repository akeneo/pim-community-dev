<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

/**
 * Boolean attribute transformer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipTransformer implements PropertyTransformerInterface, EntityUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
    }

    public function setValue($object, array $columnInfo, $data, array $options = array())
    {
    }
}
