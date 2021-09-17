<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use PhpSpec\ObjectBehavior;

final class SearchAttributeOptionsParametersSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SearchAttributeOptionsParameters::class);
    }

    public function it_returns_the_attribute_code(): void
    {
        $this->setAttributeCode('an_attribute_code');
        $this->getAttributeCode()->shouldReturn('an_attribute_code');
    }

    public function it_returns_the_attribute_option_codes(): void
    {
        $this->setAttributeOptionCodes(['', '']);
        $this->getAttributeOptionCodes()->shouldReturn(['']);
    }

    public function it_returns_the_search(): void
    {
        $this->setSearch('search_value');
        $this->getSearch()->shouldReturn('search_value');
    }

    public function it_returns_the_locale(): void
    {
        $this->setLocale('en_US');
        $this->getLocale()->shouldReturn('en_US');
    }
}
