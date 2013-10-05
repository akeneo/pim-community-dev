<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportController extends JobInstanceController
{
    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_index")
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_create")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_show")
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_edit")
     */
    public function editAction(Request $request, $id)
    {
        return parent::editAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_remove")
     */
    public function removeAction(Request $request, $id)
    {
        return parent::removeAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_launch")
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
