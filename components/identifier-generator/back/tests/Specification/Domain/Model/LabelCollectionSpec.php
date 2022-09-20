<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelCollectionSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_an_array_key_is_not_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            1 => '',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_an_array_key_is_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            '' => '',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_a_value_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            'fr_FR' => 12,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_instantiated_with_a_stdclass()
    {
        $this->beConstructedThrough('fromNormalized', [new \stdClass()]);

        $this->normalize()->shouldBeLike((object) []);
    }

    function it_normalizes_labels()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => '',
        ]]);
        $this->normalize()->shouldBe(['en_US' => 'Sugar']);
    }

    function it_normalizes_empty_label()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);

        $this->normalize()->shouldBeLike((object) []);
    }

    function it_can_be_merged_with_other_labels()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => 'Suc',
        ]]);

        $newLabels = $this->merge(['fr_FR' => 'Sucre', 'de_DE' => 'Zucker', 'en_US' => '']);
        $newLabels->shouldNotBe($this);
        $newLabels->shouldBeLike(LabelCollection::fromNormalized([
            'fr_FR' => 'Sucre',
            'de_DE' => 'Zucker',
        ]));
    }

    function it_can_be_merged_with_an_empty_array()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'en_US' => 'Sugar',
                    'fr_FR' => '',
                ]
            ]
        );

        $newLabels = $this->merge([]);
        $newLabels->shouldNotBe($this);
        $newLabels->shouldBeLike(LabelCollection::fromNormalized(['en_US' => 'Sugar']));
        $newLabels->normalize()->shouldReturn(['en_US' => 'Sugar']);
    }

    function it_returns_a_label()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => 'Suc',
        ]]);
        $this->getLabel('en_US')->shouldReturn('Sugar');
        $this->getLabel('fr_FR')->shouldReturn('Suc');
        $this->getLabel('de_DE')->shouldReturn(null);
    }
}
