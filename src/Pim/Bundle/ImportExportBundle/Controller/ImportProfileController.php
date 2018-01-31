<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Component\Batch\Model\JobInstance;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
     * {@inheritdoc}
     *
     * @AclAncestor("pim_importexport_import_profile_edit")
     */
    public function editAction($code)
    {
        return parent::editAction($code);
    }

    /**
     * {@inheritdoc}
     */
    protected function getJobType()
    {
        return JobInstance::TYPE_IMPORT;
    }
}
