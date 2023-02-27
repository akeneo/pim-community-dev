<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function it_sets_attribute_as_required_if_type_is_identifier()
    {
        $this->isRequired()->shouldReturn(false);
        $this->setType('pim_catalog_identifier');
        $this->isRequired()->shouldReturn(true);
    }

    function it_returns_the_guidelines()
    {
        $this->addGuidelines('en_US', 'the guidelines');
        $this->addGuidelines('fr_FR', 'les indications');
        $this->getGuidelines()->shouldReturn([
            'en_US' => 'the guidelines',
            'fr_FR' => 'les indications',
        ]);
    }

    public function it_gets_a_translation_even_if_the_locale_case_is_wrong(
        AttributeTranslation $translationEn,
    )
    {
        $translationEn->getLocale()->willReturn('EN_US');
        $this->addTranslation($translationEn);

        $this->getTranslation('en_US')->shouldReturn($translationEn);
    }
}
