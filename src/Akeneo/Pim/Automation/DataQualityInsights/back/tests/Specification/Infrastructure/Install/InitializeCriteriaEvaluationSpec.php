<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class InitializeCriteriaEvaluationSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        Connection $db,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        QueueJobLauncher $queueJobLauncher,
        SimpleFactoryInterface $userFactory
    ) {
        $this->beConstructedWith($featureFlag, $db, $createProductsCriteriaEvaluations, $jobInstanceRepository, $tokenStorage, $queueJobLauncher, $userFactory);
    }

    public function it_is_initializable()
    {
        $this->beAnInstanceOf(InitializeCriteriaEvaluation::class);
    }

    public function it_throws_an_exception_if_feature_is_disabled($featureFlag)
    {
        $featureFlag->isEnabled()->willReturn(false);

        $this->shouldThrow(\RuntimeException::class)->during('initialize');
    }

    public function it_initialize_nothing_if_their_is_no_product(
        $featureFlag,
        $db,
        $jobInstanceRepository,
        $tokenStorage,
        $userFactory,
        UserInterface $user,
        JobInstance $jobInstance,
        TokenInterface $token,
        ResultStatement $resultStatement
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $userFactory->create()->willReturn($user);
        $user->setUsername(UserInterface::SYSTEM_USER_NAME)->shouldBeCalled();
        $user->getRoles()->willReturn([]);

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $jobInstanceRepository->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)->willReturn($jobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')->willReturn($resultStatement);
        $resultStatement->fetch()->willReturn(['nb' => 0]);

        $this->initialize();
    }

    public function it_initialize_products_evaluation(
        $featureFlag,
        $db,
        $createProductsCriteriaEvaluations,
        $jobInstanceRepository,
        $tokenStorage,
        $queueJobLauncher,
        $userFactory,
        UserInterface $user,
        JobInstance $jobInstance,
        TokenInterface $token,
        ResultStatement $countResultStatement,
        ResultStatement $productIdsResultStatement
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $userFactory->create()->willReturn($user);
        $user->setUsername(UserInterface::SYSTEM_USER_NAME)->shouldBeCalled();
        $user->getRoles()->willReturn([]);

        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $jobInstanceRepository->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)->willReturn($jobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null')->willReturn($countResultStatement);
        $countResultStatement->fetch()->willReturn(['nb' => 99]);

        $db->query(Argument::any())->willReturn($productIdsResultStatement);

        $ids = range(1, 100);
        $productIdsResultStatement->fetchAll(FetchMode::COLUMN, 0)->willReturn($ids);

        $createProductsCriteriaEvaluations->create(Argument::type('array'))->shouldBeCalled();
        $queueJobLauncher->launch($jobInstance, $user, [EvaluateProductsCriteriaParameters::PRODUCT_IDS => $ids])->shouldBeCalled();

        $this->initialize();
    }
}
