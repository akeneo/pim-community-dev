<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Writer;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Webmozart\Assert\Assert;

class ProductWriter implements ItemWriterInterface
{
    public function __construct(
        private UpsertProductHandler $upsertProductHandler,
    ) {
    }

    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, UpsertProductCommand::class);

        /** @var UpsertProductCommand $upsertProductCommand */
        foreach ($items as $upsertProductCommand) {
            ($this->upsertProductHandler)($upsertProductCommand);
        }
    }
}
