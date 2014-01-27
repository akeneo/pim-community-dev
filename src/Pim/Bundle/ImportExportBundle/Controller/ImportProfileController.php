<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportProfileController extends JobProfileController
{
    /**
     * List the import profiles
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_importexport_import_profile_index")
     * @return array
     */
    public function indexAction(Request $request)
    {
        return array(
            'jobType'    => $this->getJobType(),
            'connectors' => $this->connectorRegistry->getJobs($this->getJobType())
        );
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_create")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_show")
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_edit")
     */
    public function editAction(Request $request, $id)
    {
        return parent::editAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_remove")
     */
    public function removeAction(Request $request, $id)
    {
        return parent::removeAction($request, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_launch")
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
