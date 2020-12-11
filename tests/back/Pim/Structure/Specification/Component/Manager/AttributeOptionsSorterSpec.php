<?php

namespace Specification\Akeneo\Pim\Structure\Component\Manager;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class AttributeOptionsSorterSpec extends ObjectBehavior
{
    public function let(BulkSaverInterface $optionSaver)
    {
        $this->beConstructedWith($optionSaver);
    }

    public function it_sorts_options(
        AttributeInterface $attribute,
        AttributeOption $size,
        AttributeOption $width,
        $optionSaver
    ) {
        $size->getId()->willReturn(45);
        $siteValue = (new AttributeOptionValue())->setLocale('en_US')->setValue('big');
        $size->addOptionValue($siteValue);

        $width->getId()->willReturn(18);
        $widthValue = (new AttributeOptionValue())->setLocale('en_US')->setValue('wide');
        $width->addOptionValue($widthValue);

        $attribute->getOptions()->willReturn([$size, $width]);

        $size->setSortOrder(2)->shouldBeCalled();
        $width->setSortOrder(1)->shouldBeCalled();

        $optionSaver->saveAll([0 => $size, 1 => $width])->shouldBeCalled();

        $this->updateSorting($attribute, [18 => 1, 45 => 2]);
    }
}
