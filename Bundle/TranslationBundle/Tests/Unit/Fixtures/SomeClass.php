<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Fixtures;

class SomeClass
{
    const STRING_TO_TRANSLATE = 'oro.translation.some_string';
    const STRING_NOT_TO_TRANSLATE = 'some_vendor.translation.some_string';

    public function someFunction()
    {
        $someVariable = 'oro.translation.some_another_string';

        return $someVariable;
    }
}
