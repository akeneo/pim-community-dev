<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Fixtures;

class SomeClass
{
    const STRING_TO_TRANSLATE     = 'oro.translation.some_string';
    const STRING_NOT_TO_TRANSLATE = 'some_vendor.some_bundle.service_id';

    public function someFunction()
    {
        $someVariable = 'oro.translation.some_another_string';

        return $someVariable;
    }
}
