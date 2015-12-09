<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
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

    function it_sets_and_gets_values(Collection $values)
    {
        $this->getValues()->shouldReturnAnInstanceOf('Doctrine\Common\Collections\Collection');
        $this->setValues($values);
        $this->getValues()->shouldReturn($values);
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
}
