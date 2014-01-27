<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use JMS\Serializer\Annotation\Exclude;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * A batch operation operator
 * Contains a list of products and a batch operation to apply on them
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Exclude
 */
class MassEditActionOperator
{
    /**
     * @var MassEditActionInterface $operation
     * @Exclude
     */
    protected $operation;

    /**
     * @var string $operationAlias
     */
    protected $operationAlias;

    /**
     * @var ProductManager $manager
     * @Exclude
     */
    protected $manager;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * The defined operations, indexed by code
     *
     * @var MassEditActionInterface[] $operations
     * @Exclude
     */
    protected $operations = array();

    /**
     * The default acls for each configured operation, indexed by code
     *
     * @var string[] $acls
     */
    protected $acls = array();

    /**
     * @param ProductManager $manager
     * @param SecurityFacade $securityFacade
     */
    public function __construct(ProductManager $manager, SecurityFacade $securityFacade)
    {
        $this->manager = $manager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Register a batch operation into the operator
     *
     * @param string                  $alias
     * @param MassEditActionInterface $operation
     * @param string                  $acl
     *
     * @throws \InvalidArgumentException
     */
    public function registerMassEditAction($alias, MassEditActionInterface $operation, $acl = null)
    {
        if (array_key_exists($alias, $this->operations)) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is already registered', $alias));
        }
        $this->operations[$alias] = $operation;
        if ($acl) {
            $this->acls[$alias] = $acl;
        }
    }

    /**
     * Get the operation choices to present in the batch operator form
     *
     * @return array
     */
    public function getOperationChoices()
    {
        $choices = array();

        foreach (array_keys($this->operations) as $alias) {
            if ($this->isGranted($alias)) {
                $choices[$alias] = sprintf('pim_enrich.mass_edit_action.%s.label', $alias);
            }
        }

        return $choices;
    }

    /**
     * Get the batch operation
     *
     * @return MassEditActionInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set the batch operation alias
     * (Also set the batch operation if the alias is registered
     *
     * @param string $operationAlias
     *
     * @throws InvalidArgumentException when the alias is not registered
     * @return MassEditActionInterface
     */
    public function setOperationAlias($operationAlias)
    {
        if (!$this->isGranted($operationAlias)) {
            throw new \InvalidArgumentException(sprintf('Operation %s is not allowed', $operationAlias));
        }
        $this->operationAlias = $operationAlias;

        if (!isset($this->operations[$operationAlias])) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is not registered', $operationAlias));
        }

        $this->operation = $this->operations[$operationAlias];

        return $this;
    }

    /**
     * Get the operation alias
     *
     * @return string
     */
    public function getOperationAlias()
    {
        return $this->operationAlias;
    }

    /**
     * Delegate the batch operation initialization to the chosen operation adapter
     *
     * @param array $productIds
     */
    public function initializeOperation($productIds)
    {
        if ($this->operation) {
            $this->operation->initialize($this->getProducts($productIds));
        }
    }

    /**
     * Delegate the batch operation execution to the chosen operation adapter
     *
     * @param array $productIds
     */
    public function performOperation(array $productIds)
    {
        if ($this->operation) {
            $this->operation->perform($this->getProducts($productIds));
        }
    }

    /**
     * Get the product matching the stored product ids
     *
     * @param integer[] $productIds
     *
     * @return ProductInterface[]
     *
     * @throws InvalidArgumentException
     */
    protected function getProducts(array $productIds)
    {
        $products = $this->manager->findByIds($productIds);
        if (!$products) {
            throw new \InvalidArgumentException(sprintf('No product were found with ids %s', join(', ', $productIds)));
        }

        return $products;
    }

    /**
     * Returns true if the operation is allowed for the current user
     *
     * @param string $operationAlias
     *
     * @return boolean
     */
    protected function isGranted($operationAlias)
    {
        return !isset($this->acls[$operationAlias]) ||
            $this->securityFacade->isGranted($this->acls[$operationAlias]);
    }
}
