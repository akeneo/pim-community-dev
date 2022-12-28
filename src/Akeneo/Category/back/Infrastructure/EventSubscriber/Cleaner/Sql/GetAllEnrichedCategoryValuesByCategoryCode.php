<?php

namespace Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql;

interface GetAllEnrichedCategoryValuesByCategoryCode
{
    public function execute(): array;
}
