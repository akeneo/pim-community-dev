<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

/**
 * Write product model data into a XLSX file on the local filesystem
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelWriter extends AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration()
    {
        return ['type' => 'xlsx'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $productModel)
    {
        return $productModel['code'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilePath()
    {
        $parameters = $this->stepExecution->getJobParameters();

        return $parameters->get('filePathProductModel');
    }
}
