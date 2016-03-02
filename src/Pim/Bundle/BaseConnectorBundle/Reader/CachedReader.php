<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;

/**
 * Cached reader
 *
 * Can be used to have an item step which receives data from a previous step.
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CachedReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return array_shift($this->data);
    }

    /**
     * Adds an item to the reader
     *
     * @param array $item
     * @param mixed $index
     *
     * @return CachedReader
     */
    public function addItem(array $item, $index = null)
    {
        if (null === $index) {
            $this->data[] = $item;
        } else {
            $this->data[$index] = $item;
        }

        return $this;
    }

    /**
     * Returns an item from the reader's data
     *
     * @param mixed $index
     *
     * @return array
     */
    public function getItem($index)
    {
        return isset($this->data[$index])
            ? $this->data[$index]
            : null;
    }
}
