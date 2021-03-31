<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Normalization;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Webmozart\Assert\Assert;

class RecordProcessor implements ItemProcessorInterface
{
    public function process($item): array
    {
        Assert::isInstanceOf($item, Record::class);

        return $item->normalize();
    }
}
