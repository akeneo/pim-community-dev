<?php

namespace Pim\Bundle\BatchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\Common\Util\Inflector;

/**
 * Form type for step configuration
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepConfigurationType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form      = $event->getForm();
                $step      = $event->getData();
                $reader    = $step->getReader();
                $processor = $step->getProcessor();
                $writer    = $step->getWriter();

                $form->add(
                    $factory->createNamed(
                        'reader',
                        new StepElementConfigurationType(),
                        $reader,
                        array(
                            'label' => sprintf('pim_batch.%s.title', $this->getTableizedClassName($reader)),
                            'auto_initialize' => false,
                        )
                    )
                );

                $form->add(
                    $factory->createNamed(
                        'processor',
                        new StepElementConfigurationType(),
                        $processor,
                        array(
                            'label' => sprintf('pim_batch.%s.title', $this->getTableizedClassName($processor)),
                            'auto_initialize' => false,
                        )
                    )
                );

                $form->add(
                    $factory->createNamed(
                        'writer',
                        new StepElementConfigurationType(),
                        $writer,
                        array(
                            'label' => sprintf('pim_batch.%s.title', $this->getTableizedClassName($writer)),
                            'auto_initialize' => false,
                        )
                    )
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\\Bundle\\BatchBundle\\Step\\ItemStep',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_batch_step_configuration';
    }

    private function getTableizedClassName($object)
    {
        $classname = get_class($object);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return Inflector::tableize($classname);
    }
}
