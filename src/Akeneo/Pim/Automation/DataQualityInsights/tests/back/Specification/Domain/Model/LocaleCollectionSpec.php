<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

final class LocaleCollectionSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_constructed_with_empty_array()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_constructed_with_an_array_of_locale_codes()
    {
        $aLocaleCode = new LocaleCode('en_US');

        $this->beConstructedWith([$aLocaleCode]);

        $this->getIterator()->shouldBeLike(new \ArrayIterator(['en_US' => $aLocaleCode]));
    }

    public function it_cannot_be_constructed_with_something_else_than_an_array_of_locale_codes()
    {
        $aFamilyCode = new FamilyCode('accessories');

        $this->beConstructedWith([$aFamilyCode]);
        $this->shouldThrow(\TypeError::class)->duringInstantiation();
    }

    public function it_adds_locale_to_the_collection()
    {
        $aLocaleCode = new LocaleCode('en_GB');
        $anotherLocaleCode = new LocaleCode('en_US');

        $this->beConstructedWith([$aLocaleCode]);

        $this->add($anotherLocaleCode);

        $this->getIterator()->shouldBeLike(new \ArrayIterator([
            'en_GB' => $aLocaleCode,
            'en_US' => $anotherLocaleCode
        ]));
    }

    public function it_deduplicate_locale()
    {
        $aLocaleCode = new LocaleCode('en_US');
        $anotherLocaleCode = new LocaleCode('en_US');

        $this->beConstructedWith([$aLocaleCode]);

        $this->add($anotherLocaleCode);

        $this->getIterator()->shouldBeLike(new \ArrayIterator([
            'en_US' => new LocaleCode('en_US'),
        ]));
    }
}
