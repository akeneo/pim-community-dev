<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Aggregation;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FacetItem
{
    /** @var mixed */
    private $key;

    /** @var int */
    private $count;

    public function __construct($key, int $count)
    {
        $this->key = $key;
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
