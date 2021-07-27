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
use PhpSpec\ObjectBehavior;

class BooleanColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
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

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène']);
    }

    function it_returns_the_validations()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
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
                'data_type' => 'boolean',
                'code' => 'is_allergenic',
                'labels' => ['en_US' => 'Is allergenic', 'fr_FR' => 'Allergène'],
                'validations' => (object)[],
            ]
        );
    }
}
