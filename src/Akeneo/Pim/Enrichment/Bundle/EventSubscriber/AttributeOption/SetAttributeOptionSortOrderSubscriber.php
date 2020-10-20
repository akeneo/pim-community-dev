<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\GetAttributeOptionsMaxSortOrder;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeOptionSortOrderSubscriber implements EventSubscriberInterface
{
    /** @var GetAttributeOptionsMaxSortOrder */
    private $getAttributeOptionsMaxSortOrder;

    public function __construct(GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder)
    {
        $this->getAttributeOptionsMaxSortOrder = $getAttributeOptionsMaxSortOrder;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'onPreSave',
            StorageEvents::PRE_SAVE_ALL => 'onPreSaveAll',
        ];
    }

    public function onPreSave(GenericEvent $event): void
    {
        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $subject = $event->getSubject();
        if ($subject instanceof AttributeOptionInterface) {
            $this->setSortOrders([$subject]);

            return;
        }
        if ($subject instanceof AttributeInterface) {
            $this->setSortOrders($subject->getOptions()->toArray());
        }
    }

    public function onPreSaveAll(GenericEvent $event)
    {
        $subjects = $event->getSubject();
        if (!is_array($subjects)) {
            return;
        }

        if (current($subjects) instanceof AttributeOptionInterface) {
            $this->setSortOrders($subjects);

            return;
        }

        if (current($subjects) instanceof AttributeInterface) {
            $options = [];
            foreach ($subjects as $attribute) {
                foreach ($attribute->getOptions() as $option) {
                    $options[] = $option;
                }
            }
            $this->setSortOrders($options);
        }
    }

    /**
     * @param AttributeOptionInterface[] $options
     */
    private function setSortOrders(array $options): void
    {
        $options = array_filter($options, function (AttributeoptionInterface $option) {
            return null === $option->getSortOrder();
        });
        if (empty($options)) {
            return;
        }

        $attributeCodes = array_unique(array_map(function (AttributeOptionInterface $option): string {
            return $option->getAttribute()->getCode();
        }, $options));

        $currentMaxSortOrders = $this->getAttributeOptionsMaxSortOrder->forAttributeCodes(
            array_values($attributeCodes)
        );

        foreach ($options as $option) {
            $attributeCode = $option->getAttribute()->getCode();

            if (!isset($currentMaxSortOrders[$attributeCode])) {
                $sortOrder = $currentMaxSortOrders[$attributeCode] = 0;
            } else {
                $sortOrder = ++$currentMaxSortOrders[$attributeCode];
            }
            $option->setSortOrder($sortOrder);
        }
    }
}
