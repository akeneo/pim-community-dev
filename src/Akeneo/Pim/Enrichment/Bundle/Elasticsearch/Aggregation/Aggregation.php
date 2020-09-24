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

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Aggregation;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Aggregation
{
    /** @var string */
    private $name;

    /** @var Bucket[] */
    private $buckets = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addBucket(string $key, int $count): void
    {
        $this->buckets[$key] =  new Bucket($key, $count);
    }

    public function getCountForKey(string $key): int
    {
        $bucket = $this->buckets[$key] ?? null;

        return null !== $bucket ? $bucket->getCount() : 0;
    }
}
