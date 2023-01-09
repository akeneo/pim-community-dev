<?php

namespace Akeneo\Category\Application\Query;

interface GetAllEnrichedCategoryValuesByCategoryCode
{
    /**
     * @return array<string, string>
     */
    public function execute(): array;
}
