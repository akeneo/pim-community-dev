<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class FamilyNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_a_family_to_a_franklin_array_format(
        FamilyInterface $family,
        FamilyTranslation $enTranslation,
        FamilyTranslation $frTranslation,
        ArrayCollection $familyTranslations,
        \ArrayIterator $translationsIterator
    ): void {
        $family->getCode()->willReturn('tshirt');
        $family->getLabel()->willReturn('T-shirt');
        $family->getTranslations()->willReturn($familyTranslations);

        $familyTranslations->getIterator()->willReturn($translationsIterator);
        $translationsIterator->valid()->willReturn(true, true, false);
        $translationsIterator->current()->willReturn($frTranslation, $enTranslation);
        $translationsIterator->next()->shouldBeCalled();
        $translationsIterator->rewind()->shouldBeCalled();

        $frTranslation->getLocale()->willReturn('fr_FR');
        $frTranslation->getLabel()->willReturn('T-shirt');

        $enTranslation->getLocale()->willReturn('en_US');
        $enTranslation->getLabel()->willReturn('T-shirt');

        $this->normalize($family)->shouldReturn([
            'code' => 'tshirt',
            'label' => [
                'fr_FR' => 'T-shirt',
                'en_US' => 'T-shirt',
            ],
        ]);
    }
}
