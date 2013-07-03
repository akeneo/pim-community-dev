<?php

namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

class AddressCollectionTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @var PropertyPath
     */
    protected $propertyPath;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param string $property
     * @param string $entityClass
     */
    public function __construct($property, $entityClass)
    {
        $this->property = $property;
        $this->propertyPath = new PropertyPath($this->property);
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_BIND     => 'postBind',
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::PRE_BIND      => 'preBind'
        );
    }

    /**
     * Pre set empty collection elements.
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $data = $event->getData();

        if (!$data) {
            return;
        }

        /** @var Collection $addresses */
        $addresses = $this->propertyPath->getValue($data);

        if ($addresses->isEmpty()) {
            $this->propertyPath->setValue(
                $data,
                array(new $this->entityClass())
            );
        }
    }

    /**
     * Removes empty collection elements.
     *
     * @param FormEvent $event
     */
    public function postBind(FormEvent $event)
    {
        $data = $event->getData();

        if (!$data) {
            return;
        }

        /** @var Collection $addresses */
        $addresses = $this->propertyPath->getValue($data);
        $notEmptyAddresses = $addresses->filter(
            function (AbstractTypedAddress $address) {
                return !$address->isEmpty();
            }
        );

        $this->propertyPath->setValue($data, $notEmptyAddresses);
    }

    /**
     * Remove empty addresses to prevent validation.
     *
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();

        if (!$data) {
            return;
        }

        $addresses = array();
        $hasPrimary = false;
        if ($data && array_key_exists($this->property, $data)) {
            foreach ($data[$this->property] as $addressRow) {
                if (!$this->isArrayEmpty($addressRow)) {
                    $hasPrimary = $hasPrimary || (array_key_exists('primary', $addressRow) && $addressRow['primary']);
                    $addresses[] = $addressRow;
                }
            }
        }

        // Set first non empty address for new item as primary
        if ($addresses) {
            if ((!array_key_exists('id', $data) || !$data['id']) && !$hasPrimary) {
                $first = array_shift($addresses);
                $first['primary'] = true;
                array_unshift($addresses, $first);
            }
            $data[$this->property] = $addresses;
            $event->setData($data);
        }
    }

    /**
     * Check if array is empty
     *
     * @param array $array
     * @return bool
     */
    protected function isArrayEmpty($array)
    {
        foreach ($array as $val) {
            if (is_array($val)) {
                if (!$this->isArrayEmpty($val)) {
                    return false;
                }
            } elseif (!empty($val)) {
                return false;
            }
        }
        return true;
    }
}
