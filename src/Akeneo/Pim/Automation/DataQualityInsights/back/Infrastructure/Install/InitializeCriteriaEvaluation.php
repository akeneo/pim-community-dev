<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluateProductsCriteriaParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class InitializeCriteriaEvaluation
{
    private const BATCH_OF_PRODUCTS = 100;

    /** @var FeatureFlag */
    private $featureFlag;

    /** @var Connection */
    private $db;

    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var QueueJobLauncher */
    private $queueJobLauncher;

    /** @var SimpleFactoryInterface */
    private $userFactory;

    public function __construct(
        FeatureFlag $featureFlag,
        Connection $db,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        QueueJobLauncher $queueJobLauncher,
        SimpleFactoryInterface $userFactory
    ) {
        $this->featureFlag = $featureFlag;
        $this->db = $db;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->queueJobLauncher = $queueJobLauncher;
        $this->userFactory = $userFactory;
    }

    public function initialize()
    {
        if (false === $this->featureFlag->isEnabled()) {
            throw new \RuntimeException(
                'Data Quality Insights Feature is not enabled. This migration script is skipped.'
            );
        }

        $user = $this->impersonateSystemUser();

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME);

        $query = $this->db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null');
        $nb = $query->fetch();

        if ($nb['nb']===0) {
            return;
        }

        $steps = ceil($nb['nb']/intval(self::BATCH_OF_PRODUCTS));

        for ($i = 0; $i<$steps; $i++) {
            $stmt = $this->db->query('select id from pim_catalog_product where product_model_id is null LIMIT ' . $i*intval(self::BATCH_OF_PRODUCTS) . ',' . intval(self::BATCH_OF_PRODUCTS));
            $ids = array_map(function ($id) {
                return intval($id);
            }, $stmt->fetchAll(FetchMode::COLUMN, 0));

            $productIds = array_map(function ($id) {
                return new ProductId($id);
            }, $ids);

            $this->createProductsCriteriaEvaluations->create($productIds);

            $jobParameters = [
                EvaluateProductsCriteriaParameters::PRODUCT_IDS => $ids,
            ];

            $this->queueJobLauncher->launch($jobInstance, $user, $jobParameters);
        }
    }

    private function impersonateSystemUser()
    {
        $user = $this->userFactory->create();
        $user->setUsername(UserInterface::SYSTEM_USER_NAME);

        $token = new SystemUserToken($user);
        $this->tokenStorage->setToken($token);

        return $this->tokenStorage->getToken()->getUser();
    }
}
