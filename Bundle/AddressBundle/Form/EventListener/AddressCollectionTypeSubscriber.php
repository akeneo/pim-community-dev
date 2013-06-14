<?php

namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\AddressBundle\Entity\TypedAddress;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddressCollectionTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @param string $property
     */
    public function __construct($property)
    {
        $this->property = $property;
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

        $method = $this->getMethodName();
        if ($data && method_exists($data, $method)) {
            /** @var Collection $addresses */
            $addresses = $data->$method();
            if ($addresses->isEmpty()) {
                $addresses->add(new TypedAddress());
            }
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

        $method = $this->getMethodName();
        if ($data && method_exists($data, $method)) {
            /** @var Collection $addresses */
            $addresses = $data->$method();
            /** @var TypedAddress $item */
            $hasDefault = false;
            foreach ($addresses as $item) {
                $hasDefault = $hasDefault || $item->isDefault();
                if ($item->isEmpty()) {
                    $addresses->removeElement($item);
                }
            }
            if (!$addresses->isEmpty() && !$hasDefault) {
                $addresses->first()->setDefault(true);
            }
        }
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
        if ($data && array_key_exists($this->property, $data)) {
            foreach ($data[$this->property] as $addressRow) {
                if (!$this->isArrayEmpty($addressRow)) {
                    $addresses[] = $addressRow;
                }
            }
        }

        if ($addresses) {
            $data[$this->property] = $addresses;
        } else {
            unset($data[$this->property]);
        }
        $event->setData($data);
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

    /**
     * Get getter method name.
     *
     * @return string
     */
    protected function getMethodName()
    {
        return 'get' . ucfirst($this->property);
    }
}
