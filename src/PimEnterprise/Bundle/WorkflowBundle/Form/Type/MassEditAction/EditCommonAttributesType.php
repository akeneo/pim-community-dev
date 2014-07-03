<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\EditCommonAttributesType as BaseEditCommonAttributesType;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorInterface;

/**
 * Form type of the EditCommonAttributes operation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EditCommonAttributesType extends BaseEditCommonAttributesType implements ChangesCollectorAwareInterface
{
    /** @var array */
    protected $changes = [];

    /** @var ChangesCollectorInterface */
    protected $collector;

    /**
     * {@inheritdoc}
     */
    public function setCollector(ChangesCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function(FormEvent $event)
            {
                $data = $event->getData();
                $form = $event->getForm();

                if (isset($data['values'])) {

                    $valueFields = $form->get('values');
                    foreach ($data['values'] as $key => $changes) {
                        $this->collector->add(
                            $key,
                            $changes,
                            $valueFields[$key]->getData()
                        );
                    }

                }
            }
        );
    }
}
