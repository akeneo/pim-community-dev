<?php
declare(strict_types=1);
namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetExistingReferenceDataCodes
{
    public function fromReferenceDataNameAndCodes(string $referenceDataName, array $codes): array;
}
