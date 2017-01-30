<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController
{
    /**
     * List channels
     *
     * @Template
     * @AclAncestor("pim_enrich_channel_index")
     *
     * @return array|Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create channel
     *
     * @Template("PimEnrichBundle:Channel:form.html.twig")
     * @AclAncestor("pim_enrich_channel_create")
     *
     * @return array|Response
     */
    public function createAction()
    {
        return [];
    }

    /**
     * Edit channel
     *
     * @Template("PimEnrichBundle:Channel:form.html.twig")
     * @AclAncestor("pim_enrich_channel_edit")
     *
     * @param string $code
     *
     * @return array|Response
     */
    public function editAction($code)
    {
        return ['code' => $code];
    }
}
