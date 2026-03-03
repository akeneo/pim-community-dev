<?php

namespace Akeneo\Category\back\tests\Integration\Domain\ValueObject;;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Code;
use Webmozart\Assert\InvalidArgumentException;

class CodeIntegration extends CategoryTestCase
{
    public function testItCanBeConstructedWithAString(): void
    {
        $code = new Code('my_code');
        $this->assertEquals('my_code', (string) $code);
    }

    public function testItThrowsExceptionWhenCodeIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Code('');
    }

    public function testItCanHaveZeroAsCode(): void
    {
        $code = new Code('0');
        $this->assertEquals('0', (string) $code);
    }
}
