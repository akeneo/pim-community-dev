<?php

namespace spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TranslatableUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TranslatableUpdater::class);
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

    function it_set_a_new_label_when_the_new_label_is_not_empty(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale('en_US')->shouldBeCalled();
        $object->getTranslation()->willReturn($translation);
        $translation->setLabel('foo')->shouldBeCalled();

        $this->update($object, ['en_US' => 'foo']);
    }
}

interface LabelTranslationInterface extends TranslationInterface
{
    public function getLabel();

    public function setLabel($label);
}
