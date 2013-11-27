<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\Type\AttributeRequirementType;
use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeAsLabelSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableCodeFieldSubscriber;

/**
 * Type for family form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyType extends AbstractType
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var AddAttributeRequirementsSubscriber
     */
    protected $requirementsSubscriber;

    /**
     * Constructor
     *
     * @param ChannelManager $channelManager
     */
    public function __construct(
        ChannelManager $channelManager,
        AddAttributeRequirementsSubscriber $requirementsSubscriber
    ) {
        $this->channelManager         = $channelManager;
        $this->requirementsSubscriber = $requirementsSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder
            ->add('code');

        $this->addLabelField($builder);

        $builder
            ->add('attributeRequirements', 'collection', array('type' => new AttributeRequirementType()))
            ->addEventSubscriber(new AddAttributeAsLabelSubscriber($factory))
            ->addEventSubscriber($this->requirementsSubscriber)
            ->addEventSubscriber(new DisableCodeFieldSubscriber());
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     *
     * @return \Pim\Bundle\CatalogBundle\Form\Type\FamilyType
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\FamilyTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\Family',
                'property_path'     => 'translations'
            )
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Family'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_family';
    }
}
