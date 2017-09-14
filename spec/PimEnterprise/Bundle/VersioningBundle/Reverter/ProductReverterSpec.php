<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Reverter;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PimEnterprise\Bundle\VersioningBundle\Exception\RevertException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductReverterSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ArrayConverterInterface $converter
    ) {
        $this->beConstructedWith($registry, $productUpdater, $saver, $validator, $translator, $converter);
    }

    function it_reverts_an_entity(
        $registry,
        $productUpdater,
        $saver,
        $converter,
        $validator,
        Version $version,
        ObjectRepository $repository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationsList,
        ValueCollectionInterface $productValueCollection
    ) {
        $snapshot = [
            'identifier' => 'sku-1',
            'values'     => [
                'a_string' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my string',
                ],
            ],
        ];
        $version->getResourceName()->willReturn('foo');
        $version->getSnapshot()->willReturn($snapshot);
        $version->getResourceId()->willReturn('baz');

        $product->getValues()->willReturn($productValueCollection);
        $product->getVariantGroup()->willReturn(null);
        $productValueCollection->clear()->shouldBecalled();

        $registry->getRepository('foo')->willReturn($repository);
        $repository->find('baz')->willReturn($product);

        $standardProduct = ['standardArray'];
        $converter->convert($snapshot)->willReturn($standardProduct);

        $productUpdater->update($product, $standardProduct)->shouldBeCalled();

        $saver->save($product)->shouldBeCalled();

        $validator->validate($product)->willReturn($violationsList);
        $violationsList->count()->willReturn(0);

        $this->revert($version);
    }

    function it_throws_an_exception_when_the_product_is_not_valid(
        $registry,
        $productUpdater,
        $converter,
        $validator,
        $translator,
        Version $version,
        ObjectRepository $repository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationsList,
        ValueCollectionInterface $productValueCollection
    ) {
        $snapshot = [
            'identifier' => 'sku-1',
            'values'     => [
                'a_string' => [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'my string',
                ],
            ],
        ];
        $version->getResourceName()->willReturn('foo');
        $version->getSnapshot()->willReturn($snapshot);
        $version->getResourceId()->willReturn('baz');

        $translator->trans('flash.error.revert.product_has_variant')->willReturn('Product can not be reverted because it belongs to a variant group');
        $translator->trans('flash.error.revert.product')->willReturn('This version can not be restored. Some errors occurred during the validation.');

        $product->getValues()->willReturn($productValueCollection);
        $product->getVariantGroup()->willReturn(null);
        $productValueCollection->clear()->shouldBecalled();

        $registry->getRepository('foo')->willReturn($repository);
        $repository->find('baz')->willReturn($product);

        $standardProduct = ['standardArray'];
        $converter->convert($snapshot)->willReturn($standardProduct);

        $productUpdater->update($product, $standardProduct)->shouldBeCalled();

        $validator->validate($product)->willReturn($violationsList);
        $violationsList->count()->willReturn(1);

        $this
            ->shouldThrow(
                new RevertException('This version can not be restored. Some errors occurred during the validation.')
            )
            ->during('revert', [$version]);
    }
}
