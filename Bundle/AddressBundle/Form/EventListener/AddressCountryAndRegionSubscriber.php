<?php
namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository;

class AddressCountryAndRegionSubscriber implements EventSubscriberInterface
{
    private $om;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param FormFactoryInterface $factory
     */
    public function __construct(ObjectManager $om, FormFactoryInterface $factory)
    {
        $this->om = $om;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }

    /**
     * Removes or adds a state field based on the country set.
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $address = $event->getData();
        $form = $event->getForm();

        if (null === $address) {
            return;
        }

        /** @var $country \Oro\Bundle\AddressBundle\Entity\Country */
        $country = $address->getCountry();

        if (null === $country) {
            return;
        }

        if ($country->hasRegions()) {
            if ($form->has('state')) {
                $config = $form->get('state')->getConfig()->getOptions();
                unset($config['choice_list']);
                unset($config['choices']);
            } else {
                $config = array();
            }

            $config['country'] = $country;
            $config['query_builder'] = $this->getRegionClosure($country);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'state',
                    'oro_region',
                    $address->getState(),
                    $config
                )
            );
        }
    }

    /**
     * Removes or adds a state field based on the country set on submitted form.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        /** @var $country \Oro\Bundle\AddressBundle\Entity\Country */
        $country = $this->om->getRepository('OroAddressBundle:Country')
            ->find(isset($data['country']) ? $data['country'] : false);

        if ($country && $country->hasRegions()) {
            $form = $event->getForm();

            $config = $form->get('state')->getConfig()->getOptions();
            unset($config['choice_list']);
            unset($config['choices']);

            $config['country'] = $country;
            $config['query_builder'] = $this->getRegionClosure($country);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'state',
                    'oro_region',
                    null,
                    $config
                )
            );

            // do not allow saving text state in case when state was checked from list
            unset($data['state_text']);
        } else {
            // do not allow saving state select in case when state was filled as text
            unset($data['state']);
        }

        $event->setData($data);
    }

    /**
     * @param Country $country
     * @return callable
     */
    protected function getRegionClosure(Country $country)
    {
        return function (RegionRepository $regionRepository) use ($country) {
            return $regionRepository->getCountryRegionsQueryBuilder($country);
        };
    }
}
