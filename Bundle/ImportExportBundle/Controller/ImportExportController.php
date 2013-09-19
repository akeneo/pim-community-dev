<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

class ImportExportController extends Controller
{
    /**
     * @Route("/export/instant/{entityName}/{processorAlias}", name="oro_importexport_export_instant")
     * @Acl(
     *      id="oro_importexport_export_instant",
     *      name="Instant entity export",
     *      description="Instant entity export",
     *      parent="oro_importexport"
     * )
     */
    public function instantExportAction($entityName, $processorAlias)
    {
        return new Response('instant export action');
    }
}
