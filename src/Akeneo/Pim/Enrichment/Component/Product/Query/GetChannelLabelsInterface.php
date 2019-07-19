<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * Executes query to get the stored labels of a collection of arrays.
 *
 * Returns an array like:
 * [
 *      'print' => [
 *          'en_US' => 'Print',
 *          'fr_FR' => 'Impression',
 *          'de_DE' => 'Drucken'
 *      ], ...
 * ]
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetChannelLabelsInterface
{
    public function forChannelCodes(array $channelCodes): array;
}
