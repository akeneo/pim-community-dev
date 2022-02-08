<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Platform\TailoredImport\Application\Common\DataMapping;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteDataMappingHandler
{
    public function handle(ExecuteDataMappingQuery $executeDataMappingQuery)
    {
        $userIntents = [];

        /** @var DataMapping $dataMapping */
        foreach ($executeDataMappingQuery->getDataMappingCollection()->getIterator() as $dataMapping) {
            /**
             * How do we structure the code to determine the type of target property OR attribute,
             *  - Attribute: deal with action type set OR add, and ValueUserIntent based on primitive type of the cellData value
             *  - Property:  Determine the correct
             */
            $cellData = implode("", array_map(static fn(string $uuid) => $executeDataMappingQuery->getRow()->getCellData($uuid), $dataMapping->getSources()));
            /** TODO Iterate over operation */
            $target = $dataMapping->getTarget();
            /** Do we create UserIntentCollection ? */
            $userIntents[] = new SetTextValue($target->getCode(), $target->getLocale(), $target->getChannel(), $cellData);
        }

        return new UpsertProductCommand(valuesUserIntent: $userIntents);
    }
}