<?php

namespace spec\Pim\Component\ReferenceData\Factory\ProductValue;

use Acme\Bundle\AppBundle\Entity\Fabric;
use Acme\Bundle\AppBundle\Model\ProductValue;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataCollectionProductValueFactory;
use Prophecy\Argument;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionProductValueFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductValue::class, Argument::any());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataCollectionProductValueFactory::class);
    }

    function it_creates_an_empty_reference_data_multi_select_product_value(AttributeInterface $attribute)
    {
        $this->beConstructedWith(ProductValue::class, 'pim_reference_data_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            []
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_reference_data_multi_select_product_value(
        AttributeInterface $attribute
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_reference_data_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            []
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_reference_data_multi_select_product_value(
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_reference_data_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [$silk, $cotton]
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveReferenceData([$silk, $cotton]);
    }

    function it_creates_a_localizable_and_scopable_reference_data_multi_select_product_value(
        AttributeInterface $attribute,
        Fabric $silk,
        Fabric $cotton
    ) {
        $this->beConstructedWith(ProductValue::class, 'pim_reference_data_catalog_multiselect');
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_simpleselect')->shouldReturn(false);
        $this->supports('pim_reference_data_catalog_multiselect')->shouldReturn(true);

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('reference_data_multi_select_attribute');
        $attribute->getAttributeType()->willReturn('pim_reference_data_catalog_multiselect');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('fabrics');

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            [$silk, $cotton]
        );

        $productValue->shouldReturnAnInstanceOf(ProductValue::class);
        $productValue->shouldHaveAttribute('reference_data_multi_select_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHaveReferenceData([$silk, $cotton]);
    }

    public function getMatchers()
    {
        return [
            'haveAttribute'     => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'     => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'        => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'        => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'       => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'           => function ($subject) {
                return $subject->getData() instanceof ArrayCollection && [] === $subject->getData()->toArray();
            },
            'haveReferenceData' => function ($subject, $expected) {
                $fabrics = $subject->getData()->toArray();

                $hasFabrics = false;
                foreach ($fabrics as $fabric) {
                    $hasFabrics = in_array($fabric, $expected);
                }

                return $hasFabrics;
            },
        ];
    }
}
