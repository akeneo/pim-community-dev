<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;

class TagSelectType extends AbstractType
{
    /**
     * @var TagSubscriber
     */
    protected $subscriber;

    public function __construct(TagSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'required'     => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder->add(
            'autocomplete',
            'oro_tag_autocomplete'
        );

        $builder->add(
            'all',
            'hidden'
        );

        $builder->add(
            'owner',
            'hidden'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_tag_select';
    }
}
