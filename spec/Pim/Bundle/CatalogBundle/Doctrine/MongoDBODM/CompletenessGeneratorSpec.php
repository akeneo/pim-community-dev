<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Factory\CompletenessFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        DocumentManager $manager,
        CompletenessFactory $completenessFactory,
        ValidatorInterface $validator,
        ProductInterface $product,
        ProductValueInterface $value,
        AbstractAttribute $varchar,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        AttributeRequirement $requireVarcharMobile,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Channel $mobile,
        Locale $enUs,
        Locale $frFr,
        Locale $nlNl,
        ConstraintViolationListInterface $noViolation,
        ConstraintViolationListInterface $withViolations,
        ChannelManager $channelManager,
        FamilyRepository $familyRepository
    ) {
        $varchar->getCode()->willReturn('attr_varchar');
        $varchar->isLocalizable()->willReturn(true);
        $varchar->isScopable()->willReturn(true);

        $product->getFamily()->willReturn($family);

        $enUs->getCode()->willReturn('en_US');
        $frFr->getCode()->willReturn('fr_FR');
        $nlNl->getCode()->willReturn('nl_NL');

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn(array($enUs, $frFr, $nlNl));

        $mobile->getCode()->willReturn('mobile');
        $mobile->getLocales()->willReturn(array($enUs, $frFr, $nlNl));

        $requireVarcharEcommerce->getChannel()->willReturn($ecommerce);
        $requireVarcharEcommerce->getAttribute()->willReturn($varchar);
        $requireVarcharEcommerce->isRequired()->willReturn(true);

        $requireVarcharMobile->getChannel()->willReturn($mobile);
        $requireVarcharMobile->getAttribute()->willReturn($varchar);
        $requireVarcharMobile->isRequired()->willReturn(true);

        $noViolation->count()->willReturn(0);
        $withViolations->count()->willReturn(1);

        $product->getValue(Argument::cetera())->willReturn($value);
        $channelManager->getFullChannels()->willReturn(array($ecommerce, $mobile));

        $this->beConstructedWith($manager, $completenessFactory, $validator, 'pim_product_class', $channelManager, $familyRepository);
    }

    function it_is_a_completeness_generator()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
    }

    function it_schedules_product_completeness($product, $manager, PersistentCollection $completenesses)
    {
        $manager->flush($product)->shouldNotBeCalled();
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->clear()->shouldBeCalled();

        $this->schedule($product);
    }

    function it_builds_product_completenesses_with_one_channel_and_three_locales(
        $product,
        $validator,
        $completenessFactory,
        $family,
        $requireVarcharEcommerce,
        $ecommerce,
        $frFr,
        $enUs,
        $nlNl,
        $noViolation,
        $withViolations
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce));

        $violationsList = array($noViolation, $withViolations, $withViolations);

        $validator->validateValue(Argument::cetera())->will(function () use (&$violationsList) {
            return array_shift($violationsList);
        });

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $nlNl, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    function it_doesnt_build_product_completenesses_without_family (
        $product,
        $validator,
        $manager
    ) {
        $product->getFamily()->willReturn(null);

        $manager->flush($product)->shouldNotBeCalled();
        $product->setCompletenesses(new ArrayCollection())->shouldNotBeCalled();

        $this->generateMissingForProduct($product);
    }
    function it_builds_product_completenesses_with_two_channels_and_three_locales(
        $product,
        $validator,
        $completenessFactory,
        $family,
        $requireVarcharEcommerce,
        $requireVarcharMobile,
        $ecommerce,
        $mobile,
        $frFr,
        $enUs,
        $nlNl,
        $noViolation,
        $withViolations
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce, $requireVarcharMobile));

        $violationsList = [$noViolation, $withViolations, $noViolation, $noViolation, $noViolation, $withViolations];

        $validator->validateValue(Argument::cetera())->will(function () use (&$violationsList) {
            return array_shift($violationsList);
        });

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $nlNl, 0, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $frFr, 0, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $nlNl, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }
}
