<?php

namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Oro\Bundle\AddressBundle\Entity\TypedAddress;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Common\Collections\ArrayCollection;
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
            /** @var ArrayCollection $addresses */
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
            /** @var ArrayCollection $addresses */
            $addresses = $data->$method();
            foreach ($addresses as $item) {
                $str = (string)$item;
                if (empty($str)) {
                    $addresses->removeElement($item);
                }
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
                $str = implode('', $addressRow);
                if (!empty($str)) {
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
     * Get getter method name.
     *
     * @return string
     */
    protected function getMethodName()
    {
        return 'get' . ucfirst($this->property);
    }
}
