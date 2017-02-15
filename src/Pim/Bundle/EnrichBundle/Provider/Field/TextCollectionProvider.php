<?php

namespace Pim\Bundle\EnrichBundle\Provider\Field;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Field provider for the Text collection attribute type.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextCollectionProvider extends BaseFieldProvider
{
    protected $fields = [
        AttributeTypes::TEXT_COLLECTION => 'akeneo-text-collection-field',
    ];
}
