<?php

namespace Pim\Bundle\GridBundle\Action\Export;

/**
 * Interface defining export actions for datagrid
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ExportActionInterface
{
    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource();

    /**
     * Export action name
     *
     * @return string
     */
    public function getName();

    /**
     * Actions options (route, ACL resource, etc.)
     *
     * @return array
     */
    public function getOptions();

    /**
     * Get specific option name
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name);
}
