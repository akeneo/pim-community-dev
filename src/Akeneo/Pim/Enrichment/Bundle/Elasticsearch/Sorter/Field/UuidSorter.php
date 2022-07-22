<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UuidSorter extends BaseFieldSorter
{
    public function __construct(array $supportedFields = [])
    {
        parent::__construct(['id']);
    }
}
