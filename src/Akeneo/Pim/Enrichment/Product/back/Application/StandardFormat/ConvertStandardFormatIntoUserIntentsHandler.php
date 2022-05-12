<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\UserIntentFactoryRegistry;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandler
{
    public function __construct(private UserIntentFactoryRegistry $userIntentFactoryRegistry)
    {
    }

    /**
     * @return UserIntent[]
     */
    public function __invoke(GetUserIntentsFromStandardFormat $getUserIntentsFromStandardFormat): array
    {
        $standardFormat = $getUserIntentsFromStandardFormat->standardFormat();

        $userIntents = [];
        foreach ($standardFormat as $fieldName => $data) {
            $userIntents[] = $this->userIntentFactoryRegistry->fromStandardFormatField($fieldName, $data);
        }

        return \array_filter($userIntents);
    }

    private function isDataEmpty(mixed $data, string $attributeType): bool
    {
        if (null === $data
            || [] === $data
            || '' === $data
            || [''] === $data
            || [null] === $data) {
            return true;
        }

        if ($attributeType === AttributeTypes::METRIC && (null === $data['amount']
        || [] === $data['amount']
        || '' === $data['amount']
        || [''] === $data['amount']
        || [null] === $data['amount'])) {
            return true;
        }

        return false;
    }
}
