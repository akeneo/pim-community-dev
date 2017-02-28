<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Copy a text collection
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextCollectionAttributeCopier extends BaseAttributeCopier
{
    /** @var string[] */
    protected $supportedFromTypes = [AttributeTypes::TEXT_COLLECTION];

    /** @var string[] */
    protected $supportedToTypes = [AttributeTypes::TEXT_COLLECTION];
}
