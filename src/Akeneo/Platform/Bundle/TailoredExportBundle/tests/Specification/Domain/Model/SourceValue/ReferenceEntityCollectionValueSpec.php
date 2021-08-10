<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityCollectionValue;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['record_code_1', 'record_code_2', 'record_code_3']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ReferenceEntityCollectionValue::class);
    }

    public function it_throws_an_exception_if_record_codes_are_invalid()
    {
        $this->beConstructedWith(['record_code_1', 2, 'record_code_3']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_record_codes()
    {
        $this->getRecordCodes()->shouldReturn(['record_code_1', 'record_code_2', 'record_code_3']);
    }
}
