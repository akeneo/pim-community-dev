<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Export controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_importexport_export",
 *      type="action",
 *      name="Export profile manipulation",
 *      description="Export profile manipulation",
 *      parent="pim_importexport"
 * )
 */
class ExportController extends JobInstanceController
{
    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_export_index",
     *      type="action",
     *      name="View export profile list",
     *      description="View export profile list",
     *      parent="pim_importexport_export"
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
     *      id="pim_importexport_export_create",
     *      type="action",
     *      name="Create an export profile",
     *      description="Create an export profile",
     *      parent="pim_importexport_export"
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
     *      id="pim_importexport_export_show",
     *      type="action",
     *      name="View the configuration of an export profile",
     *      description="View the configuration of an export profile",
     *      parent="pim_importexport_export"
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
     *      id="pim_importexport_export_edit",
     *      type="action",
     *      name="Edit the configuration of an export profile",
     *      description="Edit the configuration of an export profile",
     *      parent="pim_importexport_export"
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
     *      id="pim_importexport_export_remove",
     *      type="action",
     *      name="Remove an export profile",
     *      description="Remove an export profile",
     *      parent="pim_importexport_export"
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
     *      id="pim_importexport_export_launch",
     *      type="action",
     *      name="Launch an export profile",
     *      description="Launch an export profile",
     *      parent="pim_importexport_export"
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
        return JobInstance::TYPE_EXPORT;
    }
}
