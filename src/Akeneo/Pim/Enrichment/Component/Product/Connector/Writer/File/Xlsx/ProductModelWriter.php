<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx;

use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;

/**
 * Write product model data into a XLSX file on the local filesystem
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelWriter extends AbstractItemMediaWriter
{
    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        return ['type' => 'xlsx'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $productModel): string
    {
        return $productModel['code'];
    }
}
