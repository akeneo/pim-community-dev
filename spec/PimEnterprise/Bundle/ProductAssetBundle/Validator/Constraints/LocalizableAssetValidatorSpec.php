<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints\LocalizableAsset;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocalizableAssetValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $localeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($localeRepository);
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_an_asset(
        $context,
        LocalizableAsset $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_adds_violations_if_asset_contains_a_single_reference_with_a_locale(
        $context,
        AssetInterface $asset,
        ReferenceInterface $reference,
        LocaleInterface $locale,
        LocalizableAsset $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $asset->getReferences()->willReturn([$reference]);
        $asset->getCode()->willReturn('myasset');
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $violationData = [
            '%asset%' => 'myasset'
        ];
        $context->buildViolation($constraint->unexpectedLocaleMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($asset, $constraint);
    }

    function it_adds_violations_if_asset_contains_several_references_but_not_all_localized(
        $context,
        AssetInterface $asset,
        ReferenceInterface $localizedReference,
        ReferenceInterface $notlocalizedReference,
        LocaleInterface $locale,
        LocalizableAsset $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $asset->getReferences()->willReturn([$localizedReference, $notlocalizedReference]);
        $asset->getCode()->willReturn('myasset');
        $localizedReference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $notlocalizedReference->getLocale()->willReturn(null);

        $violationData = [
            '%asset%' => 'myasset'
        ];
        $context->buildViolation($constraint->expectedLocaleMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($asset, $constraint);
    }
}
