<?php

namespace Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql;

interface UpdateCategoryEnrichedValues
{
    public function execute(array $enrichedValuesByCode): void;
}
