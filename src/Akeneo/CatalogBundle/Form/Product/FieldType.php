<?php
namespace Akeneo\CatalogBundle\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Akeneo\CatalogBundle\Entity\Product\Field;

/**
 * Product field form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FieldType extends AbstractType
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
        $builder->add('label');
        $builder->add('type');
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'akeneo_catalog_productfield';
    }

}