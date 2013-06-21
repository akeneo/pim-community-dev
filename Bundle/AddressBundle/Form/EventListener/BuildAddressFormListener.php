<?php
namespace Oro\Bundle\AddressBundle\Form\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\AddressBundle\Entity\Country;

class BuildAddressFormListener implements EventSubscriberInterface
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
            FormEvents::PRE_BIND     => 'preBind'
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
            } else {
                $config = array();
            }

            $config['query_builder'] = $this->getRegionClosure($country);

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
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        /** @var $country \Oro\Bundle\AddressBundle\Entity\Country */
        $country = $this->om->getRepository('OroAddressBundle:Country')
            ->find(isset($data['country']) ? $data['country'] : false);

        if ($country && $country->hasRegions()) {
            $config = $form->get('state')->getConfig()->getOptions();
            unset($config['choice_list']);

            $config['query_builder'] = $this->getRegionClosure($country);

            $form->add(
                $this->factory->createNamed(
                    'state',
                    'oro_region',
                    null,
                    $config
                )
            );
        }
    }

    /**
     * @param Country $country
     * @return callable
     */
    protected function getRegionClosure(Country $country)
    {
        return function (EntityRepository $er) use ($country) {
            $qb = $er->createQueryBuilder('r')
                ->where('r.country = :country')
                ->orderBy('r.name', 'ASC');
            $qb->setParameter('country', $country);

            return $qb;
        };
    }
}
