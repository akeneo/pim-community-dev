<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\FamilyVariant\AddUniqueAttributes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber automatically sets those attributes in the variant
 * attribute set corresponding to the variant product. This is done on
 * two occasions:
 * - when creating a family variant (automatically handles the non assigned attributes),
 * - when adding a unique attribute to a family that already have family variants.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUniqueAttributesToVariantProductAttributeSetSubscriber implements EventSubscriberInterface
{
    /** @var AddUniqueAttributes */
    private $addUniqueAttributes;

    /**
     * @param AddUniqueAttributes $addUniqueAttributes
     */
    public function __construct(AddUniqueAttributes $addUniqueAttributes)
    {
        $this->addUniqueAttributes = $addUniqueAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'addUniqueAttributes',];
    }

    /**
     * @param GenericEvent $event
     */
    public function addUniqueAttributes(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof FamilyVariantInterface) {
            $this->addUniqueAttributes->addToFamilyVariant($subject);
        }

        if ($subject instanceof FamilyInterface) {
            $familyVariants = $subject->getFamilyVariants();
            if ($familyVariants->isEmpty()) {
                return;
            }

            foreach ($familyVariants as $familyVariant) {
                $this->addUniqueAttributes->addToFamilyVariant($familyVariant);
            }
        }
    }
}
