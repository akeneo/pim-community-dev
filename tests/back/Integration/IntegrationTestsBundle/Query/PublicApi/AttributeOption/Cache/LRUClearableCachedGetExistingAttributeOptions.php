<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache\LRUCachedGetExistingAttributeOptions;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;

/**
 * Cached and clearable version of
 * Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache\LRUCachedGetExistingAttributeOptions
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUClearableCachedGetExistingAttributeOptions implements GetExistingAttributeOptionCodes
{
    /** @var LRUCachedGetExistingAttributeOptions */
    private $originalLRUCache;
    /** @var GetExistingAttributeOptionCodes */
    private $getExistingOptionCodes;

    public function __construct(GetExistingAttributeOptionCodes $getExistingOptionCodes)
    {
        $this->getExistingOptionCodes = $getExistingOptionCodes;
        $this->originalLRUCache = new LRUCachedGetExistingAttributeOptions($getExistingOptionCodes);
    }

    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        return $this->originalLRUCache->fromOptionCodesByAttributeCode($optionCodesIndexedByAttributeCodes);
    }

    public function clear()
    {
        $this->originalLRUCache = new LRUCachedGetExistingAttributeOptions($this->getExistingOptionCodes);
    }
}
