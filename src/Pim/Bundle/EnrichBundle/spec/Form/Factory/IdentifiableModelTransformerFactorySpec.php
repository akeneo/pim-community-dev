<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Factory;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\DataTransformerInterface;

class IdentifiableModelTransformerFactorySpec extends ObjectBehavior
{
    function it_creates_a_transformer(
        IdentifiableObjectRepositoryInterface $repository,
        DataTransformerInterface $dummyTransformer
    ) {
        $fqcn = ClassUtils::getClass($dummyTransformer->getWrappedObject());

        $this->beConstructedWith($fqcn);

        $this->create($repository, ['multiple' => false])
            ->shouldReturnAnInstanceOf($fqcn);
    }
}
