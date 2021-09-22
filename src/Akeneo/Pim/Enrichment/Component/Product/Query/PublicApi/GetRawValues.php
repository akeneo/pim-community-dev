<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query\PublicApi;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetRawValues
{
    /**
     * @param string[] $productIdentifiers
     *
     * @return iterable<string, array>
     */
    public function fromProductIdentifiers(array $productIdentifiers): iterable;
}
