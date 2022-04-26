<?php

namespace Akeneo\Category\back\tests\Integration\Stubs;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;

class AllowAll implements CollectionFilterInterface
{
    public function filterCollection($collection, $type, array $options = [])
    {
        return $collection;
    }

    public function supportsCollection($collection, $type, array $options = [])
    {
        return true;
    }
}
