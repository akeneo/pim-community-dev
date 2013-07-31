<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'fields'       => array(), // ?
                'form'         => array(), // ?
                'inherit_data' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'autocomplete',
            'oro_tag_autocomplete'
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
