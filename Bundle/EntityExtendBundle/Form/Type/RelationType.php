<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelationType extends AbstractType
{
    /**
     * @var array
     */
    protected $relations;

    public function __construct($relations)
    {
        $this->relations = $relations;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'fieldName',
            'text',
            array(
                'label' => 'Field name',
                'block' => 'general',
            )
        );

        $builder->add(
            'relation_type',
            'choice',
            array(
                'label'       => 'Relation type',
                'choices'     => array(
                    //'OneToOne'   => 'One to One',
                    'OneToMany'  => 'One to Many',
                    'ManyToOne'  => 'Many to One',
                    'ManyToMany' => 'Many to Many',
                ),
                'empty_value' => 'Please choice type...',
                'block'       => 'general',
            )
        );

        /**************************************************************************************************************
         * @OneToMany(
         *      targetEntity="Phonenumber",
         *      mappedBy="user",
         *      cascade={"persist", "remove", "merge"},
         *      orphanRemoval=true)
         * Required attributes:

        targetEntity: FQCN of the referenced target entity. Can be the unqualified class name
                      if both classes are in the same namespace. IMPORTANT: No leading backslash!

        Optional attributes:

        cascade: Cascade Option
        orphanRemoval: Boolean that specifies if orphans, inverse OneToOne entities that are not connected
                       to any owning instance, should be removed by Doctrine. Defaults to false.
        mappedBy: This option specifies the property name on the targetEntity that is the owning side of this relation.
                  Its a required attribute for the inverse side of a relationship.

         **************************************************************************************************************
         * @ManyToOne(targetEntity="Cart", cascade={"all"}, fetch="EAGER")
         *
        Defines that the annotated instance variable holds a reference that describes a many-to-one relationship
         between two entities.

        Required attributes:  targetEntity:
        Optional attributes:

        cascade: Cascade Option
        fetch: One of LAZY or EAGER
        inversedBy - The inversedBy attribute designates the field in the entity that is the inverse
                     side of the relationship.


         **************************************************************************************************************
         * @ManyToMany(targetEntity="Group", inversedBy="features")
         * @JoinTable(name="user_groups",
         *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
         *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
         *      )
         *///private $groups;

        /**
         * Inverse Side
         *
         * @ManyToMany(targetEntity="User", mappedBy="groups")
         *///private $features;

        /**
         * Defines an instance variable holds a many-to-many relationship between two entities.
         * @JoinTable is an additional, optional annotation that has reasonable default configuration values using
         * the table and names of the two related entities.

        Required attributes:  targetEntity

        Optional attributes:

        mappedBy: This option specifies the property name on the targetEntity that is the owning side of this relation.
                  Its a required attribute for the inverse side of a relationship.

        inversedBy:
        cascade:
        fetch: One of LAZY or EAGER

        NOTE For ManyToMany bidirectional relationships either side may be the owning side (the side that defines
             the @JoinTable and/or does not make use of the mappedBy attribute, thus using a default join table).
         */

        $relations = array();
        foreach ($this->relations as $relation) {
            $entityName = $moduleName = '';
            $className  = explode('\\', $relation);
            if (count($className) > 1) {
                foreach ($className as $i => $name) {
                    if (count($className) - 1 == $i) {
                        $entityName = $name;
                    } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                        $moduleName .= $name;
                    }
                }
            }

            $relations[$relation] = $moduleName . ':' . $entityName;
        }


        $builder->add(
            'target_entity',
            'choice',
            array(
                'label'       => 'Target Entity',
                'attr'        => array('class' => 'json-get-columns'),
                'choices'     => $relations,
                'empty_value' => 'Please choice entity...',
                'block'       => 'general',
            )
        );

        $builder->add(
            'referenced_column',
            'choice',
            array(
                'label'       => 'Referenced column',
                'choices'     => array(),
                'empty_value' => 'Please choice column...',
                'block'       => 'general',
            )
        );

        $builder->add(
            'cascade',
            'choice',
            array(
                'label'       => 'Cascade',
                'choices'     => array('No','Yes'),
                'empty_value' => 'Please choice type...',
                'block'       => 'general',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('config_model'));

        $resolver->setAllowedTypes(
            array(
                'config_model' => 'Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel'
            )
        );

        $resolver->setDefaults(
            array(
                //'require_js'   => array(),
                'block_config' => array(
                    'general' => array(
                        'title'    => 'General',
                        'priority' => 1,
                    )
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_relation';
    }
}
