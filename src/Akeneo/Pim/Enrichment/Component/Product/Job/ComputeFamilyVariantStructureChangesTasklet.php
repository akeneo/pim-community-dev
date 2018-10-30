<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Process\Process;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @author    Simon CARRE <simon.carre@clickandmortar.fr>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesTasklet implements TaskletInterface
{
    /** @var integer */
    const BATCH_SIZE = 50;

    /** @var string */
    const TYPE_PRODUCT_MODEL = 'ProductModel';

    /** @var string */
    const TYPE_PRODUCT = 'Product';

    /** @var integer */
    const COMMAND_SEPARATOR = ',';

    /** @var StepExecution */
    private $stepExecution;

    /** @var EntityRepository */
    private $familyVariantRepository;

    /** @var ObjectRepository */
    private $variantProductRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var KernelInterface */
    private $kernel;

    /**
     * @param EntityRepository                $familyVariantRepository
     * @param ObjectRepository                $variantProductRepository
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param KernelInterface                 $kernel
     */
    public function __construct(
        EntityRepository $familyVariantRepository,
        ObjectRepository $variantProductRepository,
        ProductModelRepositoryInterface $productModelRepository,
        KernelInterface $kernel
    )
    {
        $this->familyVariantRepository  = $familyVariantRepository;
        $this->variantProductRepository = $variantProductRepository;
        $this->productModelRepository   = $productModelRepository;
        $this->kernel                   = $kernel;

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
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $familyVariantCodes = $jobParameters->get('family_variant_codes');
        $familyVariants = $this->familyVariantRepository->findBy(['code' => $familyVariantCodes]);

        foreach ($familyVariants as $familyVariant) {
            $levelNumber = $familyVariant->getNumberOfLevel();

            while ($levelNumber >= ProductModel::ROOT_VARIATION_LEVEL) {
                if (ProductModel::ROOT_VARIATION_LEVEL === $levelNumber) {
                    $entitiesWithFamilyVariant = $this->productModelRepository->findRootProductModels($familyVariant);
                } elseif ($levelNumber === $familyVariant->getNumberOfLevel()) {
                    $entitiesWithFamilyVariant = $this->variantProductRepository->findBy([
                        'familyVariant' => $familyVariant
                    ]);
                } else {
                    $entitiesWithFamilyVariant = $this->productModelRepository->findSubProductModels($familyVariant);
                }

                $this->updateValuesOfEntities($entitiesWithFamilyVariant);
                $levelNumber--;
            }
        }
    }

    /**
     * @param EntityWithFamilyVariantInterface[] $entities
     */
    private function updateValuesOfEntities(array $entities): void
    {
        // Sort products models and products
        $productModelsIds = [];
        $productsIds      = [];
        foreach ($entities as $entity) {
            if ($entity instanceof ProductModelInterface) {
                $productModelsIds[] = $entity->getId();
            } else {
                $productsIds[] = $entity->getId();
            }
        }

        $this->processByType(self::TYPE_PRODUCT_MODEL, $productModelsIds);
        $this->processByType(self::TYPE_PRODUCT, $productsIds);
    }

    /**
     * @param string $type
     * @param array $productsIds
     *
     * @return void
     */
    private function processByType($type, $productsIds)
    {
        $pathFinder = new PhpExecutableFinder();
        foreach (array_chunk($productsIds, self::BATCH_SIZE) as $productsIdsChunk) {
            $command = sprintf(
                '%s %s/../bin/console pim:catalog:compute-family-variant-changes -t \'%s\' %s',
                $pathFinder->find(),
                $this->kernel->getRootDir(),
                $type,
                implode(self::COMMAND_SEPARATOR, $productsIdsChunk)
            );
            $process = new Process($command);
            $process->mustRun();
        }
    }
}
