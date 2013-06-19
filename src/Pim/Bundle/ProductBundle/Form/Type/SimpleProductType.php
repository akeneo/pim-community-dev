<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Product simple form type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleProductType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $builder
            ->add('values', 'collection')
            ->add('productFamily')
            ->add(
                'locales',
                'entity',
                array(
                    'label'         => 'Activated locales',
                    'required'      => true,
                    'multiple'      => true,
                    'class'         => 'Pim\Bundle\ConfigBundle\Entity\Locale',
                    'by_reference'  => false,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
                    }
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_simple_product';
    }
}
