<?php

namespace Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Pim\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

/**
 * Write product data into a XLSX file on the local filesystem
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractItemMediaWriter implements ItemWriterInterface, ArchivableWriterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigurationWriter()
    {
        return ['type' => 'xlsx'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifier(array $product)
    {
        $attributeCode = $this->attributeRepository->getIdentifierCode();

        return current($product['values'][$attributeCode])['data'];
    }
}
