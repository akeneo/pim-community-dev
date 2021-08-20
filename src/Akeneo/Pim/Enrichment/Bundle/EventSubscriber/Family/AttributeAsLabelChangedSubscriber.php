<?php


namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAsLabelChangedSubscriber implements EventSubscriberInterface
{
    private array $impactedFamilyCodes = [];

    private FamilyRepositoryInterface $familyRepository;

    private RunUniqueProcessJob $runUniqueProcessJob;

    public function __construct(FamilyRepositoryInterface $familyRepository, RunUniqueProcessJob $runUniqueProcessJob)
    {
        $this->familyRepository = $familyRepository;
        $this->runUniqueProcessJob = $runUniqueProcessJob;
    }


    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE  => 'storeFamilyCodeIfNeeded',
            StorageEvents::POST_SAVE => 'triggerFamilyRelatedProductsReindexationJob',
        ];
    }

    public function storeFamilyCodeIfNeeded(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface || is_null($subject->getId())) {
            return;
        }
        /** @var FamilyInterface $savedFamily */
        $savedFamily = $this->familyRepository->find($subject->getId());
        if ($subject->getAttributeAsLabel() === $savedFamily->getAttributeAsLabel()) {
            $impactedFamilyCodes[] = $savedFamily->getCode();
        }
    }

    public function triggerFamilyRelatedProductsReindexationJob(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }
        foreach ($this->impactedFamilyCodes as $familyCode) {
            $this->runUniqueProcessJob->run('reindex_products_after_family_attribute_as_label_changed', function ($arg) use ($familyCode) {
                return ['family_code' => $familyCode];
            });
        }
    }
}
