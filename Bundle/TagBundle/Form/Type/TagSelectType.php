<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Oro\Bundle\TagBundle\Form\DataMapper\TagMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\TagBundle\Form\Transformer\TagTransformer;
use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;

class TagSelectType extends AbstractType
{
    /**
     * @var TagTransformer
     */
    protected $transformer;

    /**
     * @var TagSubscriber
     */
    protected $subscriber;
    protected $mapper;

    public function __construct(TagTransformer $transformer, TagSubscriber $subscriber, TagMapper $mapper)
    {
        $this->transformer = $transformer;
        $this->subscriber = $subscriber;

        $this->mapper = $mapper;
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
//        $builder->addViewTransformer($this->transformer);
//        $builder->setDataMapper($this->mapper);

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
