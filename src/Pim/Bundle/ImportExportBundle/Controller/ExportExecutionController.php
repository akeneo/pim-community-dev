<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * Export execution controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportExecutionController extends JobExecutionController
{
    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_export_execution_index")
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_export_execution_show")
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_export_execution_download_log")
     */
    public function downloadLogFileAction($id)
    {
        return parent::downloadLogFileAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_export_execution_download_files")
     */
    public function downloadFilesAction($id, $archiver, $key)
    {
        return parent::downloadFilesAction($id, $archiver, $key);
    }
}
