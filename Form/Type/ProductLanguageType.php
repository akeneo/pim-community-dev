<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Pim\Bundle\ProductBundle\Entity\ProductLanguage;

/**
 * Language form type
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductLanguageType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (DataEvent $event) use ($builder) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data instanceof ProductLanguage) {
                    $form->add(
                        $builder->getFormFactory()->createNamed(
                            'active',
                            'checkbox',
                            $data->isActive(),
                            array(
                                'label' => $data->getCode(),
                            )
                        )
                    );
                }
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductLanguage'));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'language';
    }
}
