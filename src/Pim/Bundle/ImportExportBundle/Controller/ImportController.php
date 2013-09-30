<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_importexport_import",
 *      type="action",
 *      name="Import profile manipulation",
 *      description="Import profile manipulation",
 *      parent="pim_importexport"
 * )
 */
class ImportController extends JobInstanceController
{
    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_index",
     *      type="action",
     *      name="View import profile list",
     *      description="View import profile list",
     *      parent="pim_importexport_import"
     * )
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_create",
     *      type="action",
     *      name="Create an import profile",
     *      description="Create an import profile",
     *      parent="pim_importexport_import"
     * )
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_show",
     *      type="action",
     *      name="View the configuration of an import profile",
     *      description="View the configuration of an import profile",
     *      parent="pim_importexport_import"
     * )
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_edit",
     *      type="action",
     *      name="Edit the configuration of an import profile",
     *      description="Edit the configuration of an import profile",
     *      parent="pim_importexport_import"
     * )
     */
    public function editAction(Request $request, $id)
    {
        return parent::editAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_remove",
     *      type="action",
     *      name="Remove an import profile",
     *      description="Remove an import profile",
     *      parent="pim_importexport_import"
     * )
     */
    public function removeAction(Request $request, $id)
    {
        return parent::removeAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_launch",
     *      type="action",
     *      name="Launch an import profile",
     *      description="Launch an import profile",
     *      parent="pim_importexport_import"
     * )
     */
    public function launchAction(Request $request, $id)
    {
        return parent::launchAction($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    protected function getJobType()
    {
        return JobInstance::TYPE_IMPORT;
    }
}
