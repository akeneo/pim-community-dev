<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Reverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Model\Version;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VersionReverterSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        DenormalizerInterface $denormalizer,
        ProductManager $manager
    ) {
        $this->beConstructedWith($registry, $denormalizer, $manager);
    }

    function it_reverts_an_entity(
        $registry,
        $denormalizer,
        $manager,
        Version $version,
        ObjectRepository $repository,
        ProductInterface $product
    ) {
        $version->getResourceName()->willReturn('foo');
        $version->getSnapshot()->willReturn('bar');
        $version->getResourceId()->willReturn('baz');

        $registry->getRepository('foo')->willReturn($repository);
        $repository->find('baz')->willReturn('qux');

        $denormalizer->denormalize('bar', 'foo', "csv", ['entity' => 'qux'])->willReturn($product);
        $manager->saveProduct($product)->shouldBeCalled();

        $this->revert($version);
    }
}
