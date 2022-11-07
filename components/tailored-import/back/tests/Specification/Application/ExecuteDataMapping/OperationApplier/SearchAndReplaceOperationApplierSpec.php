<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\SearchAndReplaceOperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SearchAndReplaceOperationApplierSpec extends ObjectBehavior
{
    private string $uuid = '00000000-0000-0000-0000-000000000000';

    public function it_supports_search_and_replace_operation(): void
    {
        $this->supports(new SearchAndReplaceOperation($this->uuid, [
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000001',
                'replace me',
                'with this text',
                false,
            ),
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000002',
                'another replace me',
                'with this other text',
                true,
            ),
        ]))->shouldReturn(true);
    }

    public function it_applies_search_and_replace_operation(): void
    {
        $operation = new SearchAndReplaceOperation($this->uuid, [
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000001',
                'je suis un berlinois',
                'Ich bin ein berliner',
                false,
            ),
            new SearchAndReplaceValue(
                '00000000-0000-0000-0000-000000000002',
                'berliner',
                'Nantais',
                true,
            ),
        ]);

        $this->applyOperation($operation, new StringValue('Je sUis uN berlInois'))->shouldBeLike(new StringValue('Ich bin ein Nantais'));
        $this->applyOperation($operation, new StringValue('berliner BERLINER'))->shouldBeLike(new StringValue('Nantais BERLINER'));
    }

    public function it_throws_an_exception_when_value_type_is_invalid(): void
    {
        $operation = new SearchAndReplaceOperation($this->uuid, [new SearchAndReplaceValue(
            '00000000-0000-0000-0000-000000000001',
            'replace me',
            'with this text',
            false,
        )]);

        $this->shouldThrow(UnexpectedValueException::class)
            ->during('applyOperation', [$operation, new NumberValue('1')]);
    }

    public function it_throws_an_exception_when_operation_type_is_invalid(): void
    {
        $operation = new CleanHTMLOperation($this->uuid, [CleanHTMLOperation::MODE_REMOVE_HTML_TAGS]);
        $value = new StringValue('0');

        $this->shouldThrow(new UnexpectedValueException(
            $operation,
            SearchAndReplaceOperation::class,
            SearchAndReplaceOperationApplier::class,
        ))->during('applyOperation', [$operation, $value]);
    }
}
