<?php

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use PhpSpec\ObjectBehavior;

class ColumnSorterSpec extends ObjectBehavior
{
    function it_does_not_sort_column_for_the_moment()
    {
        $this->sort([
            'code',
            'family',
            'description-de_DE',
            'name',
        ])->shouldReturn([
            'code',
            'family',
            'description-de_DE',
            'name'
        ]);
    }
}
