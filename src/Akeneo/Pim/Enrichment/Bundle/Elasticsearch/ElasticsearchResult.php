<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ElasticsearchResult implements ResultInterface
{
    /** @var array */
    private $rawResult;

    public function __construct(array $rawResult)
    {
        $this->rawResult = $rawResult;
    }

    public function getRawResult(): array
    {
        return $this->rawResult;
    }
}
