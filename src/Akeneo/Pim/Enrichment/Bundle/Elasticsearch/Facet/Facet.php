<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Webmozart\Assert\Assert;

/**
 * The "facet" term is the old term in Elasticsearch, the new one is "aggregation". We use "facet" here to
 * differentiate the product model aggregation we have in PIM.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Facet
{
    /** @var string */
    private $name;

    /** @var array */
    private $counts = [];

    private function __construct(string $name, array $counts)
    {
        $this->name = $name;
        $this->counts = $counts;
    }

    public static function create(string $name, array $counts): Facet
    {
        return new Facet($name, $counts);
    }

    public function getCountForKey(string $key): int
    {
        return $this->counts[$key] ?? 0;
    }
}
