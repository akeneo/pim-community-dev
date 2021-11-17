<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasksForAttributeType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\NonExistingFamiliesException;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Webmozart\Assert\Assert;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        foreach ($this->getAttributeMasksPerAttributeTypes as $getAttributeMasksPerAttributeType) {
            $masksPerAttributeTypeCollection = $getAttributeMasksPerAttributeType->fromFamilyCodes($familyCodes);
            foreach ($masksPerAttributeTypeCollection as $familyCode => $requiredAttributeMask) {
                if (!isset($result[$familyCode])) {
                    $result[$familyCode] = $requiredAttributeMask;
                } else {
                    $result[$familyCode] = $result[$familyCode]->merge($requiredAttributeMask);
                }
            }
        }

        $nonExistingFamilyCodes = array_diff($familyCodes, \array_keys($result));
        if (\count($nonExistingFamilyCodes) > 0) {
            throw new NonExistingFamiliesException($nonExistingFamilyCodes);
        }

        return $result;
    }
}
