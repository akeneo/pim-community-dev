<?php

namespace spec\Akeneo\Component\Localization;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Akeneo\Component\Localization\Model\TranslationInterface;
use PhpSpec\ObjectBehavior;

class TranslatableUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Localization\TranslatableUpdater');
    }

    function it_set_a_new_label_when_the_new_label_not_blank(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale('en_US')->shouldBeCalled();
        $object->getTranslation()->willReturn($translation);

        $translation->getLabel()->willReturn('original label');
        $translation->setLabel('label')->shouldBeCalled();

        $this->update($object, ['en_US' => 'label']);
    }

    function it_removes_the_translation_when_the_new_label_is_null(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale('en_US')->shouldBeCalled();
        $object->getTranslation()->willReturn($translation);
        $object->removeTranslation($translation)->shouldBeCalled();

        $this->update($object, ['en_US' => null]);
    }

    function it_removes_the_translation_when_the_new_label_is_empty(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale('en_US')->shouldBeCalled();
        $object->getTranslation()->willReturn($translation);
        $object->removeTranslation($translation)->shouldBeCalled();

        $this->update($object, ['en_US' => '']);
    }
}

interface LabelTranslationInterface extends TranslationInterface
{
    public function getLabel();

    public function setLabel($label);
}
