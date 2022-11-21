<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;

class GroupTranslator implements FlatPropertyValueTranslatorInterface
{
    /** @var GetGroupTranslations */
    private $getGroupTranslations;

    public function __construct(GetGroupTranslations $getGroupTranslations)
    {
        $this->getGroupTranslations = $getGroupTranslations;
    }

    public function supports(string $columnName): bool
    {
        return 'groups' === $columnName;
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $extractedGroupCodes = $this->extractGroupCodes($values);
        $groupTranslations = $this->getGroupTranslations->byGroupCodesAndLocale($extractedGroupCodes, $locale);

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $groupCodes = explode(',', $value);
            $groupsLabelized = [];

            foreach ($groupCodes as $groupCode) {
                $groupsLabelized[] = $groupTranslations[$groupCode] ??
                    sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $groupCode);
            }

            $result[$valueIndex] = implode(',', $groupsLabelized);
        }

        return $result;
    }

    private function extractGroupCodes(array $values): array
    {
        $groupCodes = [];
        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }
            $groupCodes = array_merge($groupCodes, explode(',', $value));
        }

        return array_unique($groupCodes);
    }
}
