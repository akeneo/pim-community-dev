<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Factory;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository;
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
