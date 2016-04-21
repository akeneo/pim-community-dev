<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Pim\Component\Catalog\Validator\UniqueValuesSet;

/**
 * The UniqueValueSet class is stateful, and used when you import several product, to check if in the product batch
 * there is no unique identifier issues.
 * This listener listen the StorageEvents::POST_SAVE_ALL to reset the UniqueValueSet information, to be able to
 * work in another product batch without uniqueness issues.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetUniqueValidationSubscriber
{
    /** @var UniqueValuesSet */
    protected $uniqueValueSet;

    /**
     * @param UniqueValuesSet $uniqueValueSet
     */
    public function __construct(UniqueValuesSet $uniqueValueSet)
    {
        $this->uniqueValueSet = $uniqueValueSet;
    }

    /**
     * Reset the Unique Value Set.
     * Called on StorageEvents::POST_SAVE_ALL
     */
    public function onAkeneoStoragePostsaveall()
    {
        $this->uniqueValueSet->reset();
    }
}
