<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;

class GroupFlatTranslator implements PropertyFlatValueTranslatorInterface
{
    /**
     * @var GetGroupTranslations
     */
    private $getGroupTranslations;

    public function __construct(GetGroupTranslations $getGroupTranslations)
    {
        $this->getGroupTranslations = $getGroupTranslations;
    }

    public function support(string $columnName): bool
    {
        return $columnName === 'groups';
    }

    public function translateValues(array $values, string $locale, string $scope): array
    {
        $categoryCodesExtracted = $this->extractGroupCodes($values);
        $groupTranslations = $this->getGroupTranslations->byGroupCodesAndLocale($categoryCodesExtracted, $locale);

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $groupCodes = explode(',', $value);
            $groupsLabelized = [];

            foreach ($groupCodes as $groupCode) {
                $groupsLabelized[] = $groupTranslations[$groupCode] ?? sprintf('[%s]', $groupCode);
            }

            $result[$valueIndex] = implode(',', $groupsLabelized);
        }

        return $result;
    }

    private function extractGroupCodes(array $values): array
    {
        $groupCodes = [];
        foreach ($values as $value) {
            $groupCodes = array_merge($groupCodes, explode(',', $value));
        }

        return array_unique($groupCodes);
    }
}
