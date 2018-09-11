<?php

namespace Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Product and product model writer for mass edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var VersionManager */
    protected $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var BulkSaverInterface */
    protected $productModelSaver;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var string */
    private $jobName;

    /**
     * Constructor
     *
     * @param VersionManager                        $versionManager
     * @param BulkSaverInterface                    $productSaver
     * @param BulkSaverInterface                    $productModelSaver
     * @param EntityManagerClearerInterface         $cacheClearer
     * @param TokenStorageInterface                 $tokenStorage
     * @param JobLauncherInterface                  $jobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param string                                $jobName
     * @todo @merge On master : remove $cacheClearer. It is not used anymore. The cache is now cleared in a dedicated subscriber.
     */
    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $cacheClearer,
        TokenStorageInterface $tokenStorage = null, //TODO @merge remove following nullable before merge on 3.x
        JobLauncherInterface $jobLauncher = null,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository = null,
        string $jobName = null
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->cacheClearer = $cacheClearer;
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = array_filter($items, function ($item) {
            return $item instanceof ProductInterface;
        });
        $productModels = array_filter($items, function ($item) {
            return $item instanceof ProductModelInterface;
        });

        array_walk($items, function ($item) {
            $this->incrementCount($item);
        });

        $this->productSaver->saveAll($products);
        $this->productModelSaver->saveAll($productModels);

        if (!empty($productModels)) {
            $this->computeProductModelDescendants($productModels);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
    }

    /**
     * @param EntityWithFamilyInterface $entity
     */
    protected function incrementCount(EntityWithFamilyInterface $entity)
    {
        if ($entity->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }

    private function computeProductModelDescendants(array $productModels)
    {
        //TODO remove before merge in 3.x
        if (null === $this->tokenStorage
        || null === $this->jobInstanceRepository
        || null === $this->jobLauncher
        || null === $this->jobName
        ) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $productModelCodes = array_map(
            function ($productModel) {
                return $productModel->getCode();
            },
            $productModels
        );

        $this->jobLauncher->launch($jobInstance, $user, ['product_model_codes' => $productModelCodes]);
    }
}
