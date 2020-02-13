<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

final class DictionarySpec extends ObjectBehavior
{
    public function it_can_be_constructed_with_empty_array()
    {
        $this->beConstructedWith([]);

        $this->getIterator()->shouldBeLike(new \ArrayIterator([]));
    }

    public function it_can_be_constructed_with_an_array_of_words()
    {
        $this->beConstructedWith(['word']);

        $this->getIterator()->shouldBeLike(new \ArrayIterator(['word' => 'word']));
    }

    public function it_cannot_be_constructed_with_something_else_than_an_array_of_words()
    {
        $aFamilyCode = new FamilyCode('accessories');

        $this->beConstructedWith([$aFamilyCode, 'word']);
        $this->shouldThrow(\TypeError::class)->duringInstantiation();
    }

    public function it_adds_word_to_the_collection()
    {
        $this->beConstructedWith(['word']);

        $this->add('burger');

        $this->getIterator()->shouldBeLike(new \ArrayIterator([
            'word' => 'word',
            'burger' => 'burger'
        ]));
    }

    public function it_deduplicate_words()
    {
        $this->beConstructedWith(['word']);

        $this->add('word');

        $this->getIterator()->shouldBeLike(new \ArrayIterator([
            'word' => 'word',
        ]));
    }
}
