<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Base flexible value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleValueType extends AbstractType
{
    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @var string
     */
    protected $valueClass;

    /**
     * Constructor
     *
     * @param FlexibleManager          $flexibleManager
     * @param EventSubscriberInterface $subscriber
     */
    public function __construct(FlexibleManager $flexibleManager, EventSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
        $this->valueClass = $flexibleManager->getFlexibleValueName();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->valueClass,
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_value';
    }
}
