<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        FileStorerInterface $fileStorer,
        ProductMassActionManager $massActionManager,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        $tmpStorageDir = '/tmp/pim/file_storage'
    ) {
        $this->beConstructedWith(
            $productBuilder,
            $userContext,
            $catalogContext,
            $attributeRepository,
            $normalizer,
            $fileStorer,
            $massActionManager,
            $productUpdater,
            $productValidator,
            $internalNormalizer,
            $tmpStorageDir
        );
    }

    function it_sets_and_gets_values()
    {
        $this->getValues()->shouldReturn('');
        $this->setValues('Values');
        $this->getValues()->shouldReturn('Values');
    }

    function it_gets_the_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_edit_common_attributes');
    }

    function it_gets_the_operation_alias()
    {
        $this->getOperationAlias()->shouldReturn('edit-common-attributes');
    }

    function it_gets_the_batch_job_code()
    {
        $this->getBatchJobCode()->shouldReturn('edit_common_attributes');
    }

    function it_gets_the_item_names_it_works_on()
    {
        $this->getItemsName()->shouldReturn('product');
    }

    function it_initializes_itself(
        $productBuilder,
        $catalogContext,
        $attributeRepository,
        $massActionManager,
        LocaleInterface $deLocale,
        AttributeInterface $attr1,
        AttributeInterface $attr2,
        ProductValueInterface $prodVal1,
        ProductValueInterface $prodVal2,
        ChannelInterface $channel,
        AttributeGroupInterface $attrGroup
    ) {
        $deLocale->getCode()->willReturn('de_DE');
        $this->setLocale($deLocale);

        $catalogContext->setLocaleCode('de_DE')->shouldBeCalled();
        $attributeRepository->findWithGroups([], ['conditions' => ['unique' => 0]])
            ->shouldBeCalled()
            ->willReturn([$attr1, $attr2]);

        $attr1->setLocale('de_DE')->shouldBeCalled();
        $attr2->setLocale('de_DE')->shouldBeCalled();

        $attr1->getGroup()->willReturn($attrGroup);
        $attr2->getGroup()->willReturn($attrGroup);

        $attrGroup->setLocale('de_DE')->shouldBeCalledTimes(2);

        $massActionManager->filterLocaleSpecificAttributes([$attr1, $attr2], 'de_DE')
            ->willReturn([$attr1, $attr2]);

        // First attribute
        $deLocale->getChannels()->willReturn([$channel]);
        $attr1->isScopable()->willReturn(true);
        $attr1->getCode()->willReturn('color');
        $channel->getCode()->willReturn('mobile');

        $productBuilder->createProductValue($attr1, 'de_DE', 'mobile')
            ->shouldBeCalled()
            ->willReturn($prodVal1);
        $productBuilder->addMissingPrices($prodVal1)->shouldBeCalled();

        // Second attribute
        $attr2->isScopable()->willReturn(false);
        $attr2->getCode()->willReturn('price');

        $productBuilder->createProductValue($attr2, 'de_DE')
            ->shouldBeCalled()
            ->willReturn($prodVal2);
        $productBuilder->addMissingPrices($prodVal2)->shouldBeCalled();

        $this->initialize();

        $this->getValues()->shouldHaveCount(2);
    }

    function it_gets_all_product_attributes_and_sets_correct_locale(
        $attributeRepository,
        $massActionManager,
        AttributeInterface $attr1,
        AttributeInterface $attr2,
        AttributeGroupInterface $attrGroup,
        LocaleInterface $enLocale
    ) {
        $enLocale->getCode()->willReturn('en_US');
        $this->setLocale($enLocale);

        $attributeRepository->findWithGroups([], ['conditions' => ['unique' => 0]])
            ->shouldBeCalled()
            ->willReturn([$attr1, $attr2]);

        $attr1->setLocale('en_US')->shouldBeCalled();
        $attr2->setLocale('en_US')->shouldBeCalled();

        $attr1->getGroup()->willReturn($attrGroup);
        $attr2->getGroup()->willReturn($attrGroup);

        $massActionManager->filterLocaleSpecificAttributes([$attr1, $attr2], 'en_US')
            ->willReturn([$attr1, $attr2]);

        $attrGroup->setLocale('en_US')->shouldBeCalledTimes(2);

        $this->getAllAttributes()->shouldReturn([$attr1, $attr2]);
    }

    function it_gets_configuration($userContext, LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('fr_FR');
        $userContext->getUiLocale()->willReturn($locale);
        $this->getBatchConfig()->shouldReturn('{\"filters\":null,\"actions\":[],\"locale\":\"fr_FR\"}');
    }
}
