<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\TagBundle\Form\TagsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagSelectType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder'  => 'oro.tag.form.choose_tag',
                    'multiple'     => true,
                    'tokenSeparators' => array(',', ' '),
                    'tags' => true,
                    'extra_config' => 'multi_autocomplete',
                ),
                'autocomplete_alias' => 'tags',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new TagsTransformer($this->om, 'Oro\Bundle\TagBundle\Entity\Tag');
        $builder->addModelTransformer($transformer);
        //$builder->addViewTransformer($transformer);

        $builder->addEventListener(
            FormEvents::PRE_BIND,
            function(FormEvent $event) use ($transformer) {
                //$form = $event->getForm();
                $data = $event->getData();
                $data = $transformer->reverseTransform($data);

                $event->setData($data);
            }
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_tag_select';
    }
}
