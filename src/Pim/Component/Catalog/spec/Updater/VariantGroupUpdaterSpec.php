<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class VariantGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductValueFactory $productValueFactory,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileStorerInterface $fileStorer,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        $productTemplateClass
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $groupTypeRepository,
            $productValueFactory,
            $fileInfoRepository,
            $fileStorer,
            $pqbFactory,
            $productTemplateClass
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\VariantGroupUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_variant_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\GroupInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_variant_group(
        $attributeRepository,
        $groupTypeRepository,
        $productValueFactory,
        $fileInfoRepository,
        $pqbFactory,
        GroupInterface $variantGroup,
        AttributeInterface $mainColor,
        AttributeInterface $secondaryColor,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        ProductInterface $removedProduct,
        ProductInterface $addedProduct,
        ProductTemplateInterface $productTemplate,
        ProductQueryBuilderInterface $pqb,
        ProductValueCollectionInterface $originalValueCollection,
        ProductValueInterface $whiteValue
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->getIdentifierCode()->willReturn('code');
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($mainColor);
        $attributeRepository->findOneByIdentifier('secondary_color')->willReturn($secondaryColor);
        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('identifier', 'IN', ['foo'])->shouldBeCalled();
        $pqb->execute()->willReturn([$addedProduct]);

        $variantGroup->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setLocale('fr_FR')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);
        $variantGroup->addAxisAttribute(Argument::any())->shouldBeCalled();

        $variantGroup->removeProduct($removedProduct)->shouldBeCalled();
        $variantGroup->addProduct($addedProduct)->shouldBeCalled();
        $variantGroup->getProducts()->willReturn([$removedProduct]);

        $values = [
            'main_color'   => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'white',
                ]
            ]
        ];

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValues()->willReturn($originalValueCollection);

        $productValueFactory->create($mainColor, null, null, 'white')->willReturn($whiteValue);
        $originalValueCollection->add($whiteValue)->shouldBeCalled();
        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productTemplate->setValues($originalValueCollection)->shouldBeCalled();
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();

        $data = [
            'code'         => 'mycode',
            'axes'         => ['main_color', 'secondary_color'],
            'type'         => 'VARIANT',
            'labels'       => [
                'fr_FR' => 'T-shirt super beau',
            ],
            'values' => $values,
            'products' => ['foo']
        ];

        $this->update($variantGroup, $data, []);
    }

    function it_updates_a_variant_group_with_media_value(
        $attributeRepository,
        $groupTypeRepository,
        $productValueFactory,
        $fileInfoRepository,
        $pqbFactory,
        GroupInterface $variantGroup,
        AttributeInterface $mainColor,
        AttributeInterface $secondaryColor,
        AttributeInterface $pictureAttribute,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        ProductInterface $removedProduct,
        ProductInterface $addedProduct,
        ProductTemplateInterface $productTemplate,
        ProductQueryBuilderInterface $pqb,
        ProductValueCollectionInterface $originalValueCollection,
        FileInfoInterface $fileInfo,
        ProductValueInterface $pictureValue
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->getIdentifierCode()->willReturn('code');
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($mainColor);
        $attributeRepository->findOneByIdentifier('secondary_color')->willReturn($secondaryColor);
        $attributeRepository->findOneByIdentifier('picture')->willReturn($pictureAttribute);
        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('identifier', 'IN', ['foo'])->shouldBeCalled();
        $pqb->execute()->willReturn([$addedProduct]);

        $variantGroup->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setLocale('fr_FR')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);
        $variantGroup->addAxisAttribute(Argument::any())->shouldBeCalled();

        $variantGroup->removeProduct($removedProduct)->shouldBeCalled();
        $variantGroup->addProduct($addedProduct)->shouldBeCalled();
        $variantGroup->getProducts()->willReturn([$removedProduct]);

        $values = [
            'picture'   => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'path/to/picture.jpg',
                ]
            ]
        ];

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValues()->willReturn($originalValueCollection);

        $pictureAttribute->getBackendType()->willReturn('media');
        $fileInfoRepository->findOneByIdentifier('path/to/picture.jpg')->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/picture.jpg');

        $productValueFactory->create($pictureAttribute, null, null, 'path/to/picture.jpg')->willReturn($pictureValue);
        $originalValueCollection->add($pictureValue)->shouldBeCalled();
        $productTemplate->setValues($originalValueCollection)->shouldBeCalled();
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();

        $data = [
            'code'         => 'mycode',
            'axes'         => ['main_color', 'secondary_color'],
            'type'         => 'VARIANT',
            'labels'       => [
                'fr_FR' => 'T-shirt super beau',
            ],
            'values' => $values,
            'products' => ['foo']
        ];

        $this->update($variantGroup, $data, []);
    }

    function it_updates_an_empty_variant_group(
        $attributeRepository,
        $groupTypeRepository,
        $pqbFactory,
        GroupInterface $variantGroup,
        GroupTypeInterface $type,
        ProductTemplateInterface $productTemplate,
        ProductValueCollectionInterface $originalValueCollection
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->getIdentifierCode()->willReturn('code');
        $pqbFactory->create()->shouldNotBeCalled();

        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();

        $variantGroup->getId()->willReturn(null);
        $variantGroup->getProducts()->willReturn([]);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValues()->willReturn($originalValueCollection);

        $productTemplate->setValues($originalValueCollection)->shouldBeCalled();
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();


        $values = [
            'code'     => 'mycode',
            'axes'     => [],
            'type'     => 'VARIANT',
            'labels'   => [],
            'values'   => [],
            'products' => []
        ];

        $this->update($variantGroup, $values, []);
    }

    function it_throws_an_error_if_type_is_unknown(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'type' => 'UNKNOWN',
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'type',
                'group type',
                'The group type does not exist',
                'Pim\Component\Catalog\Updater\VariantGroupUpdater',
                'UNKNOWN'
            )
        )->during('update', [$variantGroup, $values, []]);
    }

    function it_throws_an_error_if_axis_is_unknown(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'axes' => ['unknown', 'secondary_color'],
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'axes',
                'attribute code',
                'The attribute does not exist',
                'Pim\Component\Catalog\Updater\VariantGroupUpdater',
                'unknown'
            )
        )->during('update', [$variantGroup, $values, []]);
    }

    function it_throws_an_error_if_axis_is_updated(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(42);
        $attribute = new Attribute();
        $attribute->setCode('other');
        $variantGroup->getAxisAttributes()->willReturn(new ArrayCollection([$attribute]));

        $values = [
            'code' => 'mycode',
            'axes' => ['main_color'],
        ];

        $this->shouldThrow(
            ImmutablePropertyException::immutableProperty(
                'axes',
                'main_color',
                'Pim\Component\Catalog\Updater\VariantGroupUpdater',
                'variant group'
            )
        )->during('update', [$variantGroup, $values, []]);
    }
}
