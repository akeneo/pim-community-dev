<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

/**
 * @todo: THIS CLASS IS NOT FINISHED YET
 */
class EntityFieldChoiceType extends AbstractType
{
    const NAME = 'oro_entity_field_choice';

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var EntityClassResolver
     */
    protected $entityClassResolver;

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * Constructor
     *
     * @param ManagerRegistry     $doctrine
     * @param EntityClassResolver $entityClassResolver
     * @param ConfigProvider      $entityConfigProvider
     */
    public function __construct(
        ManagerRegistry $doctrine,
        EntityClassResolver $entityClassResolver,
        ConfigProvider $entityConfigProvider
    ) {
        $this->doctrine             = $doctrine;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->entityClassResolver  = $entityClassResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that    = $this;
        $choices = function (Options $options) use ($that) {
            return empty($options['entity'])
                ? array() // return empty list if entity is not specified
                : $that->getChoices($options['entity'], $options['include_related']);
        };

        $resolver->setDefaults(
            array(
                'entity'          => null,
                'include_related' => true,
                'choices'         => $choices,
                'empty_value'     => '',
                'configs'         => array(
                    'is_translate_option'     => false,
                    'placeholder'             => 'oro.entity.form.choose_entity_field',
                    'result_template_twig'    => 'OroEntityBundle:Choice:entity_field/result.html.twig',
                    'selection_template_twig' => 'OroEntityBundle:Choice:entity_field/selection.html.twig',
                )
            )
        );
    }

    /**
     * Returns a list of choices
     *
     * @param string $entityName
     * @param bool   $includeRelated
     * @return array of entities which can be used to build a report
     *               key = full class name, value = ChoiceListItem
     */
    protected function getChoices($entityName, $includeRelated)
    {
        $choices = array();
        $this->fillChoices($choices, $this->entityClassResolver->getEntityClass($entityName), $includeRelated);
        $this->sortChoices($choices);

        return $choices;
    }

    /**
     * @param array  $choices
     * @param string $className
     * @param bool   $includeRelated
     */
    protected function fillChoices(array &$choices, $className, $includeRelated)
    {
        $metadata = $this->doctrine->getManagerForClass($className)->getClassMetadata($className);
        foreach ($metadata->getFieldNames() as $fieldName) {
            $key           = $this->getChoiceKey($className, $fieldName, $includeRelated);
            $choices[$key] = $this->getFieldLabel($className, $fieldName);
        }
        if ($includeRelated) {
            foreach ($metadata->getAssociationNames() as $associationNames) {
                $choices[$associationNames] = $associationNames;
            }
        }
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @param bool   $includeRelated
     * @return string
     */
    protected function getChoiceKey($className, $fieldName, $includeRelated)
    {
        return $includeRelated
            ? sprintf('%s::%s', $className, $fieldName)
            : $fieldName;
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @return string
     */
    protected function getFieldLabel($className, $fieldName)
    {
        if ($this->entityConfigProvider->hasConfig($className, $fieldName)) {
            return $this->entityConfigProvider->getConfig($className, $fieldName)->get('label');
        }

        return $fieldName;
    }

    /**
     * Sorts choices
     *
     * @param array $choices
     */
    protected function sortChoices(array &$choices)
    {
        // sort choices by entity name and then by field name
        uasort(
            $choices,
            function ($a, $b) {
                return strcmp((string)$a, (string)$b);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
