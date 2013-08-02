<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Oro\Bundle\TagBundle\Form\Transformer\TagTransformer;
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

    /**
     * @var TagTransformer
     */
    protected $transformer;

    public function __construct(TagSubscriber $subscriber, TagTransformer $transformer)
    {
        $this->subscriber = $subscriber;
        $this->transformer = $transformer;
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
            $builder->create(
                'all',
                'hidden'
            )->addViewTransformer($this->transformer)
        );

        $builder->add(
            $builder->create(
                'owner',
                'hidden'
            )->addViewTransformer($this->transformer)
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
