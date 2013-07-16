<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\TagBundle\Entity\TagManager;
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
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @param ObjectManager $om
     * @param TagManager    $tagManager
     */
    public function __construct(ObjectManager $om, TagManager $tagManager)
    {
        $this->om = $om;
        $this->tagManager = $tagManager;
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
        $transformer->setTagManager($this->tagManager);

        $builder->addModelTransformer($transformer);
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
