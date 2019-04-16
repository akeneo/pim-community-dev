<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListProductsQuery
{
    /** @var array */
    public $search = [];

    /** @var null|string */
    public $channelCode;

    /** @var null|string[] */
    public $localeCodes;

    /** @var null|string */
    public $searchLocaleCode;

    /** @var null|string */
    public $searchChannelCode;

    /** @var null|string[] */
    public $attributeCodes;

    /** @var string */
    public $paginationType = PaginationTypes::OFFSET;

    /** @var int */
    public $page = 1;

    /** @var null|string */
    public $searchAfter;

    /** @var int */
    public $limit;

    /** @var string */
    public $withCount = 'false';

    /** @var int */
    public $userId;

    /**
     * Returns the parameter 'with_count' typed as a boolean
     *
     * @return bool
     */
    public function withCountAsBoolean(): bool
    {
        return $this->withCount === 'true';
    }
}
