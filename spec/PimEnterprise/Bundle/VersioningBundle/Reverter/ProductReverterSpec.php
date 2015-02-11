<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Reverter;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Exception\RevertException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator;

class ProductReverterSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        DenormalizerInterface $denormalizer,
        SaverInterface $saver,
        Validator $validator
    ) {
        $this->beConstructedWith($registry, $denormalizer, $saver, $validator);
    }

    function it_reverts_an_entity(
        $registry,
        $denormalizer,
        $saver,
        $validator,
        Version $version,
        ObjectRepository $repository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationsList
    ) {
        $version->getResourceName()->willReturn('foo');
        $version->getSnapshot()->willReturn('bar');
        $version->getResourceId()->willReturn('baz');

        $registry->getRepository('foo')->willReturn($repository);
        $repository->find('baz')->willReturn('qux');

        $denormalizer->denormalize('bar', 'foo', "csv", ['entity' => 'qux'])->willReturn($product);
        $saver->save($product)->shouldBeCalled();

        $validator->validate($product)->willReturn($violationsList);
        $violationsList->count()->willReturn(0);

        $this->revert($version);
    }

    function it_throws_an_exception_when_the_product_is_not_valid(
        $registry,
        $denormalizer,
        $saver,
        $validator,
        Version $version,
        ObjectRepository $repository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationsList
    ) {
        $version->getResourceName()->willReturn('foo');
        $version->getSnapshot()->willReturn('bar');
        $version->getResourceId()->willReturn('baz');

        $registry->getRepository('foo')->willReturn($repository);
        $repository->find('baz')->willReturn('qux');

        $denormalizer->denormalize('bar', 'foo', "csv", ['entity' => 'qux'])->willReturn($product);

        $validator->validate($product)->willReturn($violationsList);
        $violationsList->count()->willReturn(1);

        $this
            ->shouldThrow(
                new RevertException('This version can not be restored. Some errors occured during the validation.')
            )
            ->during('revert', [$version]);
    }
}
