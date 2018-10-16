<?php

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Filter\NotGrantedValuesFilter;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedValuesFilterSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker, CachedObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($authorizationChecker, $localeRepository);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedValuesFilter::class);
    }

    function it_removes_not_granted_values_from_an_entity_with_values_without_variation(
        $authorizationChecker,
        EntityWithValuesInterface $entityWithValues,
        ValueCollectionInterface $values,
        ValueInterface $textValue,
        ValueInterface $colorValue,
        AttributeInterface $textAttribute,
        AttributeInterface $colorAttribute,
        \ArrayIterator $valuesIterator
    ) {
        $entityWithValues->getValues()->willReturn($values);
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->key()->willReturn(1, 2);
        $valuesIterator->current()->willReturn($textValue, $colorValue);
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttribute()->willReturn($textAttribute);
        $colorValue->getAttribute()->willReturn($colorAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $textAttribute)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $colorAttribute)->willReturn(true);
        $values->remove($textValue)->shouldBeCalled();
        $values->remove($colorValue)->shouldNotBeCalled();
        $colorValue->getLocale()->willReturn(null);

        $entityWithValues->setValues($values)->shouldBeCalled();

        $this->filter($entityWithValues)->shouldReturnAnInstanceOf(EntityWithValuesInterface::class);
    }

    function it_removes_not_granted_localizable_values_from_an_entity_with_values_without_variation(
        $authorizationChecker,
        $localeRepository,
        EntityWithValuesInterface $entityWithValues,
        ValueCollectionInterface $values,
        ValueInterface $descriptionFrValue,
        ValueInterface $descriptionEnValue,
        AttributeInterface $descriptionAttribute,
        \ArrayIterator $valuesIterator,
        LocaleInterface $frLocale,
        LocaleInterface $enLocale
    ) {
        $entityWithValues->getValues()->willReturn($values);
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->key()->willReturn(1, 2);
        $valuesIterator->current()->willReturn($descriptionFrValue, $descriptionEnValue);
        $valuesIterator->next()->shouldBeCalled();

        $descriptionFrValue->getAttribute()->willReturn($descriptionAttribute);
        $descriptionEnValue->getAttribute()->willReturn($descriptionAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $descriptionAttribute)->willReturn(true);
        $values->remove($descriptionFrValue)->shouldNotBeCalled();
        $values->remove($descriptionEnValue)->shouldNotBeCalled();
        $descriptionFrValue->getLocale()->willReturn('fr_FR');
        $descriptionEnValue->getLocale()->willReturn('en_US');

        $frLocale->getCode()->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $frLocale)->willReturn(true);
        $values->remove($descriptionFrValue)->shouldNotBeCalled();

        $enLocale->getCode()->willReturn('en_US');
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enLocale)->willReturn(false);
        $values->remove($descriptionEnValue)->shouldBeCalled();

        $entityWithValues->setValues($values)->shouldBeCalled();

        $this->filter($entityWithValues)->shouldReturnAnInstanceOf(EntityWithValuesInterface::class);
    }

    function it_removes_not_granted_values_from_an_entity_with_values_with_variation(
        $authorizationChecker,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        ValueCollectionInterface $values,
        ValueInterface $textValue,
        ValueInterface $colorValue,
        AttributeInterface $textAttribute,
        AttributeInterface $colorAttribute,
        \ArrayIterator $valuesIterator,
        FamilyVariantInterface $familyVariant
    ) {
        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $entityWithFamilyVariant->getValuesForVariation()->willReturn($values);
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->key()->willReturn(1, 2);
        $valuesIterator->current()->willReturn($textValue, $colorValue);
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttribute()->willReturn($textAttribute);
        $colorValue->getAttribute()->willReturn($colorAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $textAttribute)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $colorAttribute)->willReturn(true);
        $values->remove($textValue)->shouldBeCalled();
        $values->remove($colorValue)->shouldNotBeCalled();
        $colorValue->getLocale()->willReturn(null);

        $entityWithFamilyVariant->setValues($values)->shouldBeCalled();

        $this->filter($entityWithFamilyVariant)->shouldReturnAnInstanceOf(EntityWithValuesInterface::class);
    }

    function it_throws_an_exception_if_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('filter', [new \stdClass()]);
    }
}
