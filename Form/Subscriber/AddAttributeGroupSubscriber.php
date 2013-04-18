<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NoResultException;

/**
 * Form subscriber for product value, add relevant group for any values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeGroupSubscriber implements EventSubscriberInterface
{
    /**
     * Form factory
     *
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Doctrine object manager
     * @var ObjectManager
     */
    public $objectManager;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory       the form factory
     * @param ObjectManager        $objectManager doctrine object manager
     */
    public function __construct(FormFactoryInterface $factory, ObjectManager $objectManager)
    {
        $this->factory = $factory;
        $this->objectManager = $objectManager;
    }

    /**
     * List of subscribed events
     *
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
    }

    /**
     * Add product attribute group id to the value for rendering purpose
     *
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        $value = $event->getData();
        $form  = $event->getForm();

        if (null === $value) {
            return;
        }

        $qb = new QueryBuilder($this->objectManager);
        $qb->select('AttributeGroup.id')
            ->from('PimProductBundle:ProductAttribute', 'ProductAttribute')
            ->leftJoin('ProductAttribute.group', 'AttributeGroup')
            ->where($qb->expr()->eq('ProductAttribute.attribute', $value->getAttribute()->getId()));

        try {
            $groupId = $qb->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
        } catch (NoResultException $e) {
            $groupId = 0;
        }

        $form->add(
            $this->factory->createNamed('group', 'hidden', $groupId, array('property_path' => false))
        );
    }
}
