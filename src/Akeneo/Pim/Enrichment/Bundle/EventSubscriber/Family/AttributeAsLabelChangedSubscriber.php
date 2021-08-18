<?php


namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

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

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }


    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE  => 'persistFamilyCodeIfNeeded',
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ];
    }

    public function persistFamilyCodeIfNeeded(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
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
    }
}
