<?php
namespace Strixos\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

/**
*
* @author     Nicolas Dupont @ Strixos
* @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*
*/
class OptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Strixos\CatalogBundle\Entity\Option',
        );
    }

    public function getName()
    {
        return 'strixos_catalog_attribute_option';
    }
}