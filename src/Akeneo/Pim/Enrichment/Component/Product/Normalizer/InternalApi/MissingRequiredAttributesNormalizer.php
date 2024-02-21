<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingRequiredAttributesNormalizer implements MissingRequiredAttributesNormalizerInterface
{
    public function normalize(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $missingRequiredAttributes = [];
        /** @var ProductCompletenessWithMissingAttributeCodes $completeness */
        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->channelCode();
            if (!isset($missingRequiredAttributes[$channelCode])) {
                $missingRequiredAttributes[$channelCode] = [
                    'channel' => $channelCode,
                    'locales' => [],
                ];
            }
            $missingRequiredAttributes[$channelCode]['locales'][$completeness->localeCode()]['missing'] = array_map(
                function (string $attributeCode): array {
                    return ['code' => $attributeCode];
                },
                $completeness->missingAttributeCodes()
            );
        }

        return array_values($missingRequiredAttributes);
    }
}
