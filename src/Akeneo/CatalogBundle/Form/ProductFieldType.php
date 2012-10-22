<?php
namespace Akeneo\CatalogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for field form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');

        $builder->add('code');

        // if already exists disabled this choice
        $builder->add(
            'type', 'choice', array(
                'choices'   => BaseFieldFactory::getTypeOptions(),
                'required'  => true,
                'disabled'  => ($entity->getId())? true : false
            )
        );

        $builder->add('options', 'collection', array(
                'type' => new ProductFieldOptionType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );

//        var_dump($entity->getOptions());

//        var_dump($entity->getOption('blue'));

/*
        $builder->add(
            'options', 'collection',
            array(
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );*/

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        //    'data_class' => 'Akeneo\CatalogBundle\Entity\ProductField'
            'data_class' => 'Akeneo\CatalogBundle\Document\ProductFieldMongo'
        ));
    }

    public function getName()
    {
        return 'akeneo_catalogbundle_productfieldtype';
    }
}
