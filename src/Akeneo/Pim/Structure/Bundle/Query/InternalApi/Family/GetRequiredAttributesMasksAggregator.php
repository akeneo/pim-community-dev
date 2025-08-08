<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasksForAttributeType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Webmozart\Assert\Assert;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRequiredAttributesMasksAggregator implements GetRequiredAttributesMasks
{
    /** @var GetRequiredAttributesMasksForAttributeType[] */
    private iterable $getAttributeMasksPerAttributeTypes;

    public function __construct(iterable $getAttributeMasksPerAttributeTypes)
    {
        Assert::allIsInstanceOf($getAttributeMasksPerAttributeTypes, GetRequiredAttributesMasksForAttributeType::class);
        $this->getAttributeMasksPerAttributeTypes = $getAttributeMasksPerAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function fromFamilyCodes(array $familyCodes): array
    {
        $result = [];

        foreach ($familyCodes as $resultArrayKey) {
            $result[$resultArrayKey] = new RequiredAttributesMask($resultArrayKey, []);
        }

        foreach ($this->getAttributeMasksPerAttributeTypes as $getAttributeMasksPerAttributeType) {
            $masksPerAttributeTypeCollection = $getAttributeMasksPerAttributeType->fromFamilyCodes($familyCodes);
            foreach ($masksPerAttributeTypeCollection as $familyCode => $requiredAttributeMask) {
                if ($result[$familyCode] === []) {
                    $result[$familyCode] = $requiredAttributeMask;
                } else {
                    $result[$familyCode] = $result[$familyCode]->merge($requiredAttributeMask);
                }
            }
        }

        return $result;
    }
}
