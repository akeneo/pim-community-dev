<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;

/**
 * Writer to push products to PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductsWriter implements ItemWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        return;
    }
}
