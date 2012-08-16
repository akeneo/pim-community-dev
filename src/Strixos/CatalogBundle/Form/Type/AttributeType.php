<?php
namespace Strixos\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeType extends AbstractType
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');

        $builder->add('code');
        $builder->add('isRequired', null, array('required' => false));
        $builder->add('isUnique', null, array('required' => false));

        // if already exists disabled this choice
        $builder->add(
            'input', 'choice', array(
                'choices'   => Attribute::getFrontendInputOptions(),
                'required'  => true,
                'disabled'  => ($entity->getId())? true : false
            )
        );

        $builder->add('defaultValue', null, array('required' => false));

        $builder->add(
            'options', 'collection',
            array(
                'type'         => new OptionType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_catalog_attribute';
    }

}