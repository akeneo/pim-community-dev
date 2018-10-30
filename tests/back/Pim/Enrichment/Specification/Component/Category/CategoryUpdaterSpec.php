<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category;

use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;

class CategoryUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $categoryUpdater, TranslatableUpdater $translatableUpdater)
    {
        $this->beConstructedWith($categoryUpdater, $translatableUpdater);
    }

    function it_updates_and_translates_category_labels(
        $categoryUpdater,
        $translatableUpdater,
        CategoryInterface $category
    ) {
        $data = [
            'labels' => [
                'en_US' => 'My category'
            ]
        ];
        $options = [];

        $categoryUpdater->update($category, $data, $options)->shouldBeCalled();
        $translatableUpdater->update($category, $data['labels'])->shouldBeCalled();

        $this->update($category, $data, $options);
    }

    function it_does_not_translate_inexistant_labels(
        $categoryUpdater,
        $translatableUpdater,
        CategoryInterface $category
    ) {
        $data = [
            'data' => [
                'en_US' => 'My category'
            ]
        ];
        $options = [];

        $categoryUpdater->update($category, $data, $options)->shouldBeCalled();
        $translatableUpdater->update($category, Argument::any())->shouldNotBeCalled();

        $this->update($category, $data, $options);
    }

    function it_does_not_translate_if_object_is_not_a_translatable_interface(
        $categoryUpdater,
        $translatableUpdater,
        ProductInterface $product
    ) {
        $data = [
            'data' => [
                'en_US' => 'My category'
            ]
        ];
        $options = [];

        $categoryUpdater->update($product, $data, $options)->shouldBeCalled();
        $translatableUpdater->update($product, Argument::any())->shouldNotBeCalled();

        $this->update($product, $data, $options);
    }

    function it_throws_an_exception_if_updater_has_thrown_exception(
        $categoryUpdater,
        $translatableUpdater,
        CategoryInterface $category
    ) {
        $data = [
            'data' => [
                'en_US' => 'My category'
            ]
        ];
        $options = [];

        $categoryUpdater->update($category, $data, $options)->willThrow(new \InvalidArgumentException());
        $translatableUpdater->update($category, Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException())->during('update', [$category, $data, $options]);
    }
}
