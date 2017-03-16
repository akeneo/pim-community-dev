<?php

namespace spec\Akeneo\Component\Localization;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Akeneo\Component\Localization\Model\TranslationInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TranslatableUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Localization\TranslatableUpdater');
    }

    function it_does_no_set_a_new_label_when_the_new_label_is_blank(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale(Argument::cetera())->shouldNotBeCalled();
        $object->getTranslation()->shouldNotBeCalled();
        $translation->setLabel(Argument::cetera())->shouldNotBeCalled();

        $this->update($object, ['en_US' => '']);
    }

    function it_does_no_set_a_new_label_when_the_new_label_is_null(
        TranslatableInterface $object,
        LabelTranslationInterface $translation
    ) {
        $object->setLocale(Argument::cetera())->shouldNotBeCalled();
        $object->getTranslation()->shouldNotBeCalled();
        $translation->setLabel(Argument::cetera())->shouldNotBeCalled();

        $this->update($object, ['en_US' => null]);
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
