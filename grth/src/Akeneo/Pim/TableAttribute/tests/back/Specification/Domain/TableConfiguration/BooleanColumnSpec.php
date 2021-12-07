<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use PhpSpec\ObjectBehavior;

class BooleanColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'is_allergenic_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'is_allergenic',
                    'labels' => ['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène'],
                ],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanColumn::class);
    }

    function it_is_a_text_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('boolean');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('is_allergenic');
    }

    function it_has_an_id()
    {
        $this->id()->shouldHaveType(ColumnId::class);
        $this->id()->asString()->shouldBe('is_allergenic_cf30d88f-38c9-4c01-9821-4b39a5e3c224');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène']);
    }

    function it_is_not_required_for_completeness()
    {
        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(false);
    }

    function it_is_required_for_completeness()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'is_allergenic_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'is_allergenic',
                    'labels' => ['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène'],
                    'validations' => [],
                    'is_required_for_completeness' => true,
                ],
            ]
        );

        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(true);
    }

    function it_returns_the_validations()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'is_allergenic_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'is_allergenic',
                    'labels' => ['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène'],
                    'validations' => [],
                ],
            ]
        );

        $this->validations()->shouldBeLike(ValidationCollection::createEmpty());
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'id' => 'is_allergenic_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'data_type' => 'boolean',
                'code' => 'is_allergenic',
                'labels' => ['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène'],
                'validations' => (object)[],
                'is_required_for_completeness' => false,
            ]
        );
    }
}
