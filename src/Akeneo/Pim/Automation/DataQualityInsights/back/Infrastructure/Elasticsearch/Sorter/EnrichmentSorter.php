<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Sorter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorter;

final class EnrichmentSorter extends BaseFieldSorter
{
    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        $field .= sprintf('.enrichment.%s.%s', $channel, $locale);

        parent::addFieldSorter($field, $direction, $locale, $channel);
    }
}
