<?php

namespace Pim\Bundle\CustomEntityBundle\Configuration;

use Pim\Bundle\CustomEntityBundle\Manager\ManagerInterface;
use Pim\Bundle\CustomEntityBundle\Controller\Strategy\StrategyInterface;

/**
 * Common interface for configuration services
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfigurationInterface
{
    /**
     * Returns the name of the configuration
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the index route
     *
     * @return string
     */
    public function getIndexRoute();

    /**
     * Returns the edit route
     *
     * @return string
     */
    public function getEditRoute();

    /**
     * Returns the create route
     *
     * @return string
     */
    public function getCreateRoute();

    /**
     * Returns the remove route
     *
     * @return string
     */
    public function getRemoveRoute();

    /**
     * Returns the entity class
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * Returns the datagrid namespace
     *
     * @return string
     */
    public function getDatagridNamespace();

    /**
     * Returns the base template
     *
     * @return string
     */
    public function getBaseTemplate();

    /**
     * Returns the index template
     *
     * @return string
     */
    public function getIndexTemplate();

    /**
     * Returns the controller strategy
     *
     * @return StrategyInterface
     */
    public function getControllerStrategy();

    /**
     * Returns the create template
     *
     * @return string
     */
    public function getCreateTemplate();

    /**
     * Returns the create form type
     *
     * @return string
     */
    public function getCreateFormType();

    /**
     * Returns the create form options
     * @return array
     */
    public function getCreateFormOptions();

    /**
     * Returns the route for redirects after successful entity creation
     *
     * @param object $entity
     *
     * @return string
     */
    public function getCreateRedirectRoute($entity);

    /**
     * Returns the route parameters for redirects after successful entity creation
     *
     * @param object $entity
     *
     * @return string
     */
    public function getCreateRedirectRouteParameters($entity);

    /**
     * Returns the edit template
     *
     * @return string
     */
    public function getEditTemplate();

    /**
     * Returns the edit form type
     *
     * @return string
     */
    public function getEditFormType();

    /**
     * Returns the edit form options
     *
     * @return string
     */
    public function getEditFormOptions();

    /**
     * Returns the route for redirects after successful entity update
     *
     * @param object $entity
     *
     * @return string
     */
    public function getEditRedirectRoute($entity);

    /**
     * Returns the route parameters for redirects after successful entity update
     *
     * @param object $entity
     *
     * @return array
     */
    public function getEditRedirectRouteParameters($entity);

    /**
     * Returns the manager
     *
     * @return ManagerInterface
     */
    public function getManager();

    /**
     * Returns properties which should be added to any created object
     *
     * @return array
     */
    public function getCreateDefaultProperties();

    /**
     * Returns the options passed to the manager for entity creation
     *
     * @return array
     */
    public function getCreateOptions();

    /**
     * Returns the options passed to the manager to find entities
     *
     * @return array
     */
    public function getFindOptions();

    /**
     * Returns the options passed to the manager to generate the query builder for the datagrid
     *
     * @return array
     */
    public function getQueryBuilderOptions();
}
