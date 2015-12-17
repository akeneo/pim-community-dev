<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        $tmpStorageDir = '/tmp/pim/file_storage'
    ) {
        $this->beConstructedWith(
            $productBuilder,
            $userContext,
            $normalizer,
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

    function it_gets_configuration($userContext, LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('fr_FR');
        $expected = addslashes(json_encode([
            'filters' => null,
            'actions' => [
                'normalized_values' => '',
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => null
            ]
        ]));

        $userContext->getUiLocale()->willReturn($locale);
        $this->getBatchConfig()->shouldReturn($expected);
    }
}
