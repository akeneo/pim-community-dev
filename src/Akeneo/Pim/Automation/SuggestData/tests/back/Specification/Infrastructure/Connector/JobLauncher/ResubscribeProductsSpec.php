<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher\ResubscribeProducts;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ResubscribeProductsSpec extends ObjectBehavior
{
    public function let(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobInstanceRepository, $jobLauncher, $tokenStorage);
    }

    public function it_is_a_resubscribe_products(): void
    {
        $this->shouldImplement(ResubscribeProductsInterface::class);
        $this->shouldHaveType(ResubscribeProducts::class);
    }

    public function it_does_not_launch_the_job_if_no_product_id_is_provided($jobLauncher): void
    {
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->process([]);
    }

    // TODO APAI-450: Replace this spec by 'it_throws_an_exception_if_the_job_instance_does_not_exist'
    public function it_does_nothing_if_the_job_instance_does_not_exist($jobInstanceRepository, $jobLauncher): void
    {
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::RESUBSCRIBE_PRODUCTS)->willReturn(null);
        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->process([42, 44, 56]);
    }

    public function it_launches_the_resubscription_job_for_provided_product_ids(
        $jobInstanceRepository,
        $jobLauncher,
        $tokenStorage,
        TokenInterface $token
    ): void {
        $jobInstance = new JobInstance(
            'Suggest Data Connector',
            'franklin_insights',
            JobInstanceNames::RESUBSCRIBE_PRODUCTS
        );
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::RESUBSCRIBE_PRODUCTS)->willReturn($jobInstance);

        $user = new User();
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'filters' => [
                    [
                        'field' => 'id',
                        'operator' => 'IN',
                        'value' => ['product_42', 'product_44', 'product_999'],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->process([42, 44, 999]);
    }
}
