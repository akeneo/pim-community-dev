<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * Export report controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_importexport_import_report",
 *      name="Export report manipulation",
 *      description="Export report manipulation",
 *      parent="pim_importexport"
 * )
 */
class ImportReportController extends JobExecutionController
{
    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_report_index",
     *      name="View export report list",
     *      description="View export report list",
     *      parent="pim_importexport_import_report"
     * )
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_report_show",
     *      name="View export report details",
     *      description="View export report details",
     *      parent="pim_importexport_import_report"
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
     *      id="pim_importexport_import_report_download_log",
     *      name="Download export report log",
     *      description="Download export report log",
     *      parent="pim_importexport_import_report"
     * )
     */
    public function downloadLogFileAction($id)
    {
        return parent::downloadLogFileAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Acl(
     *      id="pim_importexport_import_report_download_files",
     *      name="Download imported files ",
     *      description="Download imported files",
     *      parent="pim_importexport_export_report"
     * )
     */
    public function downloadFilesAction($id)
    {
        return parent::downloadFilesAction($id);
    }
}
