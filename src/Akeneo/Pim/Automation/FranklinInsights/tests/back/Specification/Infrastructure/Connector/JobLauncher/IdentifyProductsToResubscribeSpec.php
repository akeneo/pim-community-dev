<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\IdentifyProductsToResubscribeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher\IdentifyProductsToResubscribe;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IdentifyProductsToResubscribeSpec extends ObjectBehavior
{
    public function let(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobInstanceRepository, $jobLauncher, $tokenStorage);
    }

    public function it_is_an_identify_products_to_resubscribe_job_launcher(): void
    {
        $this->shouldHaveType(IdentifyProductsToResubscribe::class);
        $this->shouldImplement(IdentifyProductsToResubscribeInterface::class);
    }

    public function it_does_not_do_anything_if_no_identifier_codes_are_provided($jobLauncher): void
    {
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->process([]);
    }

    public function it_launches_the_identify_products_to_resubscribe_job_with_provided_identifier_codes(
        $jobInstanceRepository,
        $jobLauncher,
        $tokenStorage,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $julia
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE)
            ->willReturn($jobInstance);

        $token->getUser()->willReturn($julia);
        $tokenStorage->getToken()->willReturn($token);

        $jobLauncher->launch(
            $jobInstance,
            $julia,
            ['updated_identifiers' => ['asin', 'upc']]
        )->shouldBeCalled();

        $this->process(['asin', 'upc']);
    }
}
