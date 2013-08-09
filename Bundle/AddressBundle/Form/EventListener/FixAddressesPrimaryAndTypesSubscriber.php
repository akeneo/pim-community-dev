<?php
namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

/**
 * When address is created/updated from single form, it will ensure the rules of one primary address and address types
 * uniqueness
 */
class FixAddressesPrimaryAndTypesSubscriber implements EventSubscriberInterface
{
    /**
     * Property path to collection of all addresses (e.g. 'owner.address' means $address->getOwner()->getAddresses())
     *
     * @var string
     */
    protected $addressesProperty;

    /**
     * @var PropertyAccess
     */
    protected $addressAccess;

    public function __construct($addressesProperty)
    {
        $this->addressesAccess = PropertyAccess::createPropertyAccessor();
        $this->addressesProperty = $addressesProperty;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SUBMIT => 'postSubmit'
        );
    }

    /**
     * Removes empty collection elements.
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        /** @var AbstractTypedAddress $address */
        $address = $event->getData();

        /** @var AbstractTypedAddress[] $allAddresses */
        $allAddresses = $this->addressesAccess->getValue($address, $this->addressesProperty);

        /**
         * Only one address must be primary
         */
        if ($address->isPrimary()) {
            foreach ($allAddresses as $otherAddresses) {
                if (!$address->isEqual($otherAddresses)) {
                    $otherAddresses->setPrimary(false);
                }
            }
            $address->setPrimary(true);
        } elseif (count($allAddresses) == 1) {
            $address->setPrimary(true);
        }

        /**
         * Two addresses must not have same types
         */
        $types = $address->getTypes();
        if (count($types)) {
            foreach ($allAddresses as $otherAddresses) {
                if (!$address->isEqual($otherAddresses)) {
                    foreach ($types as $type) {
                        $otherAddresses->removeType($type);
                    }
                }
            }
        }
    }
}
