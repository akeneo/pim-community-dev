<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface;

class FindGroupLabels implements FindGroupLabelsInterface
{
    private GetGroupTranslations $getGroupTranslations;

    public function __construct(GetGroupTranslations $getGroupTranslations)
    {
        $this->getGroupTranslations = $getGroupTranslations;
    }

    public function byCodes(array $groupCodes, string $locale): array
    {
        return $this->getGroupTranslations->byGroupCodesAndLocale($groupCodes, $locale);
    }
}
