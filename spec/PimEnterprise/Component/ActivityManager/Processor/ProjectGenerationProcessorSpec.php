<?php

namespace spec\Akeneo\ActivityManager\Component\Processor;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProjectGenerationProcessorSpec extends ObjectBehavior
{
    function let(
        ObjectDetacherInterface $objectDetacher,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        VoterInterface $attributeVoter,
        UserManager $userManager,
        StepExecution $stepExecution,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        CategoryAccessRepository $accessRepository
    ) {
        $this->beConstructedWith(
            $objectDetacher,
            $tokenStorage,
            $authorizationChecker,
            $productRepository,
            $attributeVoter,
            $userManager,
            $attributeGroupAccessManager,
            $accessRepository
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AbstractProcessor::class);
    }
}
