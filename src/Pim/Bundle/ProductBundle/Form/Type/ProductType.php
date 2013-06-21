<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Product form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends FlexibleType
{
    /**
     * Group value fields by group and scope if necessary and passes it to the view
     *
     * Exemple:
     *     array(
     *         groupId => array(
     *             'name' => groupName
     *             'attributes' => array(
     *                 attributeId => array(
     *                     'isRemovable' => true
     *                     'code'        => 'name'
     *                     'label'       => 'Name'
     *                     'sortOrder'   => 0
     *                     'value'       => FormView
     *                 )
     *                 attributeId => array(
     *                     'isRemovable' => false
     *                     'code'        => 'longDescription'
     *                     'label'       => 'Long description'
     *                     'sortOrder'   => 3
     *                     'values'      => array(
     *                         'ecommerce' => FormView
     *                         'mobile'    => FormView
     *                     'classes' => array(
     *                         'scopable' => true
     *                     )
     *                 )
     *                 attributeId => array(
     *                     'isRemovable' => true
     *                     'code'        => 'prices'
     *                     'label'       => 'Prices'
     *                     'sortOrder'   => 2
     *                     'value'       => FormView
     *                     'classes'     => array(
     *                         'currency' => true
     *                     )
     *                 )
     *             )
     *         )
     *     )
     *
     * Access it into the view through {{ form.vars.groups }}
     *
     * {@inheridoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $groups = array();

        $valueForms = $form->get('values')->getChildren();
        foreach ($valueForms as $valueForm) {
            $value          = $valueForm->getData();
            $attribute      = $value->getAttribute();
            $attributeGroup = $attribute->getVirtualGroup();

            if (!isset($groups[$attributeGroup->getId()])) {
                $groups[$attributeGroup->getId()]['name'] = $attributeGroup->getName();
            }

            if (!isset($groups[$attributeGroup->getId()]['values'][$attribute->getId()])) {
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['isRemovable'] = $value->isRemovable();
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['code']        = $attribute->getCode();
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['label']       = $attribute->getLabel();
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['sortOrder']   = $attribute->getSortOrder();
            }

            if ($value->getAttribute()->getScopable()) {
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['classes']['scopable']        = true;
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['values'][$value->getScope()] = $valueForm->createView($view->getChild('values'));
            } else {
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['value'] = $valueForm->createView($view->getChild('values'));
            }

            if ('pim_product_price_collection' === $attribute->getAttributeType()) {
                $groups[$attributeGroup->getId()]['attributes'][$attribute->getId()]['classes']['currency'] = true;
            }
        }

        foreach ($groups as $id => $group) {
            $groups[$id]['attributes'] = $this->sortAttributes($group['attributes']);
        }

        $view->vars['groups'] = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $this->addLocaleField($builder);
    }

    /**
     * Add locale field
     *
     * @param FormBuilderInterface $builder
     *
     * @return ProductType
     */
    protected function addLocaleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'locales',
            'entity',
            array(
                'required' => true,
                'multiple' => true,
                'class' => 'Pim\Bundle\ConfigBundle\Entity\Locale',
                'by_reference' => false,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
                }
            )
        );
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            new LocalizedCollectionType,
            array(
                'type'               => $this->valueFormAlias,
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
                'currentLocale'      => $options['currentLocale'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('currentLocale' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product';
    }

    /**
     * Sort an array of by the values of its sortOrder key
     *
     * @param array $attributes
     *
     * @return array
     */
    private function sortAttributes(array $attributes)
    {
        uasort(
            $attributes,
            function ($a, $b) {
                if ($a['sortOrder'] === $b['sortOrder']) {
                    return 0;
                }

                return $a['sortOrder'] > $b['sortOrder'] ? 1 : -1;
            }
        );

        return $attributes;
    }
}
