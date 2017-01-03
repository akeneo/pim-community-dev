<?php

namespace Pim\Component\Connector\Reader\File\Csv;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Product Association CSV reader
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationReader extends Reader implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getArrayConverterOptions()
    {
        return ['with_associations' => true];
    }
}
