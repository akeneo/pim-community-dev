<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditFormResolver
{
    /** @var OperationRegistry */
    protected $operationRegistry;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var MassEditChooseActionType */
    protected $chooseActionFormType;

    /**
     * Constructor.
     *
     * @param OperationRegistry        $operationRegistry
     * @param FormFactoryInterface     $formFactory
     * @param MassEditChooseActionType $chooseActionFormType
     */
    public function __construct(
        OperationRegistry $operationRegistry,
        FormFactoryInterface $formFactory,
        MassEditChooseActionType $chooseActionFormType
    ) {
        $this->operationRegistry = $operationRegistry;
        $this->formFactory = $formFactory;
        $this->chooseActionFormType = $chooseActionFormType;
    }

    /**
     * Get the form to select the operation to apply on items.
     * It contains available mass edit operation for the given $gridname.
     *
     * @param string $gridname
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getAvailableOperationsForm($gridname)
    {
        $choices = [];
        $availableOperations = $this->operationRegistry->getAllByGridName($gridname);

        foreach (array_keys($availableOperations) as $alias) {
            $choices[$alias] = sprintf('pim_enrich.mass_edit_action.%s.label', $alias);
        }

        return $this->createForm($this->chooseActionFormType, null, ['operations' => $choices]);
    }

    /**
     * Get the configuration form for the given $operationAlias.
     *
     * @param string $operationAlias
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getConfigurationForm($operationAlias)
    {
        $operation = $this->operationRegistry->get($operationAlias);

        if (! $operation instanceof ConfigurableOperationInterface) {
            throw new \LogicException(sprintf(
                'Operation with alias "%s" is not an instance of ConfigurableOperationInterface',
                $operationAlias
            ));
        }

        $form = $this->createForm($this->chooseActionFormType);
        $form->add('operation', $operation->getFormType(), $operation->getFormOptions());

        return $form;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createForm($type, $data = null, array $options = [])
    {
        return $this->formFactory->create($type, $data, $options);
    }
}
