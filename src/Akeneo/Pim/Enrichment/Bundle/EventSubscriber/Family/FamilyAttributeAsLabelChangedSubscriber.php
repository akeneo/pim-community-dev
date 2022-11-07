<?php


namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeAsLabelChangedSubscriber implements EventSubscriberInterface
{
    private array $impactedFamilyCodes = [];

    private FindAttributeCodeAsLabelForFamilyInterface $attributeCodeAsLabelForFamily;

    private Client $esClient;

    public function __construct(FindAttributeCodeAsLabelForFamilyInterface $attributeCodeAsLabelForFamily, Client $esClient)
    {
        $this->attributeCodeAsLabelForFamily = $attributeCodeAsLabelForFamily;
        $this->esClient = $esClient;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'storeFamilyCodeIfNeeded',
            StorageEvents::POST_SAVE => 'triggerFamilyRelatedProductsReindexation',
        ];
    }

    public function storeFamilyCodeIfNeeded(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface || is_null($subject->getId())) {
            return;
        }

        $oldAttributeCodeAsLabel = $this->attributeCodeAsLabelForFamily->execute($subject->getCode());
        $newAttributeCodeAsLabel = $subject->getAttributeAsLabel() ? $subject->getAttributeAsLabel()->getCode() : null;
        if ($newAttributeCodeAsLabel !== $oldAttributeCodeAsLabel) {
            $this->impactedFamilyCodes[] = $subject->getCode();
        }
    }

    public function triggerFamilyRelatedProductsReindexation(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        foreach ($this->impactedFamilyCodes as $familyCode) {
            $attributeCodeAsLabel = $subject->getAttributeAsLabel() ? $subject->getAttributeAsLabel()->getCode() : null;

            if ($attributeCodeAsLabel) {
                $this->esClient->updateByQuery([
                    'script' => [
                        'source' => "ctx._source.label = ctx._source.values[params.attributeAsLabel]",
                        'params' => ['attributeAsLabel' => sprintf('%s-text', $attributeCodeAsLabel)],
                    ],
                    'query' => [
                        'term' => ['family.code' => $familyCode]
                    ]
                ]);
            }
        }
    }
}
