<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Bulk;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This subscriber listens to PostSaveAll events on Product Models.
 *
 * When a list of product model is saved with bulk saving events, it launches a job responsible to trigger the computation
 * of its product children's completeness and the indexation of the whole subtree.
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkComputeProductModelDescendantsSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var string */
    private $jobName;

    /**
     * @param TokenStorageInterface                 $tokenStorage
     * @param JobLauncherInterface                  $jobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param string                                $jobName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        string $jobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE_ALL => 'bulkComputeProductModelDescendantsCompleteness',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function bulkComputeProductModelDescendantsCompleteness(GenericEvent $event): void
    {
        $productModels = $event->getSubject();

        if (count($productModels) === 0){
            return;
        }
        $productModelsCodes = [];
        foreach ($productModels as $productModel){
            if (!$productModel instanceof ProductModelInterface) {
                return;
            }
            $productModelsCodes[] = $productModel->getCode();
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $this->jobLauncher->launch($jobInstance, $user, ['product_model_codes' => $productModelsCodes]);
    }
}
