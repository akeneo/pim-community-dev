<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\MongoDBODM\Repository;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\MongoDBODM\Repository\AttributeRepository;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;
use PimEnterprise\Component\ActivityManager\Repository\StructuredAttributeRepositoryInterface;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(PresenterInterface $presenter)
    {
        $this->beConstructedWith($presenter);
    }

    function it_is_structured_attribute_repository()
    {
        $this->shouldImplement(StructuredAttributeRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRepository::class);
    }
}
