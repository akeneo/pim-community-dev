<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Retrieve the appropriate form corresponding to the operation
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditFormResolver
{
    /** @var OperationRegistryInterface */
    protected $operationRegistry;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var MassEditChooseActionType */
    protected $chooseActionFormType;

    /**
     * @param OperationRegistryInterface $operationRegistry
     * @param FormFactoryInterface       $formFactory
     * @param MassEditChooseActionType   $chooseActionFormType
     */
    public function __construct(
        OperationRegistryInterface $operationRegistry,
        FormFactoryInterface $formFactory,
        MassEditChooseActionType $chooseActionFormType
    ) {
        $this->operationRegistry    = $operationRegistry;
        $this->formFactory          = $formFactory;
        $this->chooseActionFormType = $chooseActionFormType;
    }

    /**
     * Get the form to select the operation to apply on items.
     * It contains available mass edit operation for the given $gridName.
     *
     * @param string $gridName
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getAvailableOperationsForm($gridName)
    {
        $choices = [];
        $availableOperations = $this->operationRegistry->getAllByGridName($gridName);

        foreach (array_keys($availableOperations) as $alias) {
            $choices[$alias] = sprintf('pim_enrich.mass_edit_action.%s.label', $alias);
        }

        return $this->formFactory->create($this->chooseActionFormType, null, ['operations' => $choices]);
    }

    /**
     * Get the configuration form for the given $operationAlias.
     *
     * @param string                              $operationAlias
     * @param ConfigurableOperationInterface|null $operation
     *
     * @throws \LogicException If operation get from $operationAlias or $operation
     *                         doesn't implements ConfigurableOperationInterface
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getConfigurationForm($operationAlias, $operation = null)
    {
        if (null === $operation) {
            $operation = $this->operationRegistry->get($operationAlias);
        }

        if (!$operation instanceof ConfigurableOperationInterface) {
            throw new \LogicException(
                sprintf(
                    'Operation with alias "%s" is not an instance of ConfigurableOperationInterface',
                    $operationAlias
                )
            );
        }

        $operation->initialize();

        $form = $this->formFactory->create($this->chooseActionFormType, $operation);
        $form->add('operation', $operation->getFormType(), $operation->getFormOptions());

        return $form;
    }
}
