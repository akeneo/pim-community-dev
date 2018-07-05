<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\EnrichedEntity\Component\Value;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product value interface for a collection of enriched entity
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface EnrichedEntityCollectionValueInterface extends ValueInterface
{
    /**
     * @return Record[]
     */
    public function getData();
}
