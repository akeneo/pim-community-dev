<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Factory\CompletenessFactory;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    public function let(
        DocumentManager $manager,
        CompletenessFactory $completenessFactory,
        ValidatorInterface $validator,
        ProductInterface $product,
        ProductValue $value,
        Attribute $varchar,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        AttributeRequirement $requireVarcharMobile,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Channel $mobile,
        Locale $enUs,
        Locale $frFr,
        ConstraintViolationListInterface $noViolation,
        ConstraintViolationListInterface $withViolations
    ) {
        $varchar->getCode()->willReturn('attr_varchar');

        $product->getFamily()->willReturn($family);

        $enUs->getCode()->willReturn('en_US');
        $frFr->getCode()->willReturn('fr_FR');

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn(array($enUs, $frFr));

        $mobile->getCode()->willReturn('mobile');
        $mobile->getLocales()->willReturn(array($enUs, $frFr));

        $requireVarcharEcommerce->getChannel()->willReturn($ecommerce);
        $requireVarcharEcommerce->getAttribute()->willReturn($varchar);

        $requireVarcharMobile->getChannel()->willReturn($mobile);
        $requireVarcharMobile->getAttribute()->willReturn($varchar);

        $noViolation->count()->willReturn(0);
        $withViolations->count()->willReturn(1);

        $product->getValue(Argument::cetera())->willReturn($value);

        $this->beConstructedWith($manager, $completenessFactory, $validator);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    public function it_is_a_completeness_generator()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    public function it_schedules_product_completeness(ProductInterface $product, DocumentManager $manager)
    {
        $manager->flush($product)->shouldBeCalled();
        $product->setCompletenesses(new ArrayCollection())->shouldBeCalled();

        $this->schedule($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    public function it_builds_product_completenesses_with_one_channel_and_two_locales (
        ProductInterface $product,
        ValidatorInterface $validator,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        Channel $ecommerce,
        Locale $frFr,
        Locale $enUs,
        ConstraintViolationListInterface $noViolation,
        ConstraintViolationListInterface $withViolations
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce));

        $violationsList = array($noViolation, $withViolations);

        $validator->validateValue(Argument::cetera())->will(function () use (&$violationsList) {
            return array_shift($violationsList);
        });

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    public function it_builds_product_completenesses_with_two_channels_and_two_locales (
        ProductInterface $product,
        ValidatorInterface $validator,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        AttributeRequirement $requireVarcharMobile,
        Channel $ecommerce,
        Channel $mobile,
        Locale $frFr,
        Locale $enUs,
        ConstraintViolationListInterface $noViolation,
        ConstraintViolationListInterface $withViolations
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce, $requireVarcharMobile));

        $violationsList = array($noViolation, $withViolations, $noViolation, $noViolation);

        $validator->validateValue(Argument::cetera())->will(function () use (&$violationsList) {
            return array_shift($violationsList);
        });

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $frFr, 0, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }
}
