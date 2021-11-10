<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasksForAttributeType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\NonExistingFamiliesException;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetRequiredAttributesMasks implements GetRequiredAttributesMasks
{
    /** @var GetRequiredAttributesMasksForAttributeType[] */
    private iterable $getAttributeMasksPerAttributeTypes;

    public function __construct(iterable $getAttributeMasksPerAttributeTypes)
    {
        $this->getAttributeMasksPerAttributeTypes = $getAttributeMasksPerAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function fromFamilyCodes(array $familyCodes): array
    {
        $result = [];
        $foundFamilyCodes = [];
        foreach ($this->getAttributeMasksPerAttributeTypes as $getAttributeMasksPerAttributeType) {
            $masksPerAttributeTypeCollection = $getAttributeMasksPerAttributeType->fromFamilyCodes($familyCodes);
            foreach ($masksPerAttributeTypeCollection as $familyCode => $requiredAttributeMask) {
                array_push($foundFamilyCodes, $familyCode);
                if (!isset($result[$familyCode])) {
                    $result[$familyCode] = $requiredAttributeMask;
                } else {
                    $formerMasks = $result[$familyCode]->masks();
                    $masksForThisAttributeType = $requiredAttributeMask->masks();
                    $result[$familyCode] = new RequiredAttributesMask($familyCode, array_merge($formerMasks, $masksForThisAttributeType));
                }
            }
        }

        $nonExistingFamilyCodes = array_diff($familyCodes, $foundFamilyCodes);
        if (count($nonExistingFamilyCodes) > 0) {
            throw new NonExistingFamiliesException($nonExistingFamilyCodes);
        }

        return $result;
    }
}
