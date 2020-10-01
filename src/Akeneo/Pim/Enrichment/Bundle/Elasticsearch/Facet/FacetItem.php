<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FacetItem
{
    /** @var string */
    private $key;

    /** @var int */
    private $count;

    private function __construct(string $key, int $count)
    {
        $this->key = $key;
        $this->count = $count;
    }

    public static function fromArray(array $array): FacetItem
    {
        Assert::keyExists($array, 'key');
        Assert::keyExists($array, 'doc_count');
        Assert::integer($array['doc_count']);

        return new FacetItem($array['key'], $array['doc_count']);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
