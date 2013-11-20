<?php

namespace Pim\Bundle\CustomEntityBundle\Configuration;

use Pim\Bundle\CustomEntityBundle\ControllerWorker\WorkerInterface;
use Pim\Bundle\CustomEntityBundle\Manager\ManagerInterface;

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
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getIndexRoute();

    /**
     * @return string
     */
    public function getEditRoute();

    /**
     * @return string
     */
    public function getCreateRoute();

    /**
     * @return string
     */
    public function getRemoveRoute();

    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * @return string
     */
    public function getDatagridNamespace();

    /**
     * @return string
     */
    public function getBaseTemplate();

    /**
     * @return string
     */
    public function getIndexTemplate();

    /**
     * @return WorkerInterface
     */
    public function getWorker();

    /**
     * @return string
     */
    public function getCreateTemplate();

    /**
     * @return string
     */
    public function getCreateFormType();

    /**
     * @return array
     */
    public function getCreateFormOptions();

    /**
     * @param  object $entity
     * @return string
     */
    public function getCreateRedirectRoute($entity);

    /**
     * @param  object $entity
     * @return string
     */
    public function getCreateRedirectRouteParameters($entity);

    /**
     * @return string
     */
    public function getEditTemplate();

    /**
     * @return string
     */
    public function getEditFormType();

    /**
     * @return string
     */
    public function getEditFormOptions();

    /**
     * @param  object $entity
     * @return string
     */
    public function getEditRedirectRoute($entity);

    /**
     * @return array
     */
    public function getEditRedirectRouteParameters($entity);

    /**
     * @return ManagerInterface
     */
    public function getManager();

    /**
     * @return array
     */
    public function getCreateDefaultProperties();

    /**
     * @return array
     */
    public function getCreateOptions();

    /**
     * @return array
     */
    public function getFindOptions();

    /**
     * @return array
     */
    public function getQueryBuilderOptions();
}
