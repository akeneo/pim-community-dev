<?php

namespace Akeneo\Pim\Structure\Component\Manager;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Sort attribute options and save them.
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsSorter
{
    /** @var BulkSaverInterface */
    protected $optionSaver;

    /**
     * Constructor
     *
     * @param BulkSaverInterface $optionSaver
     */
    public function __construct(BulkSaverInterface $optionSaver)
    {
        $this->optionSaver = $optionSaver;
    }

    /**
     * Update attribute option sorting
     *
     * @param AttributeInterface $attribute
     * @param array              $sorting
     */
    public function updateSorting(AttributeInterface $attribute, array $sorting = [])
    {
        foreach ($attribute->getOptions() as $option) {
            if (isset($sorting[$option->getId()])) {
                $option->setSortOrder($sorting[$option->getId()]);
            } else {
                $option->setSortOrder(0);
            }
        }
        $this->optionSaver->saveAll($attribute->getOptions()->toArray());
    }
}
