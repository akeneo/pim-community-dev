<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * Executes query to get the stored labels of a collection of attributes.
 *
 * Returns an array like:
 * [
 *      'name' => [
 *          'en_US' => 'Name',
 *          'fr_FR' => 'Nom',
 *          'de_DE' => 'Name'
 *      ], ...
 * ]
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeLabelsInterface
{
    public function forAttributeCodes(array $attributeCodes): array;
}
