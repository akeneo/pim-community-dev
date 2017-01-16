<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PocController
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 */
class PocController
{
    public function __construct()
    {
        // ...include deps
    }

    /**
     * abstract index
     *
     * *NotUsedAtAll
     *
     * @return array|Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create action
     *
     * @Template("PimEnrichBundle:Poc:form.html.twig")
     *
     * @return array|Response
     */
    public function createAction()
    {
        return [];
    }

    /**
     * Edit action
     *
     * @Template("PimEnrichBundle:Poc:form.html.twig")
     *
     * @param $code
     *
     * @return array|Response
     */
    public function editAction($code)
    {
        return ['code' => $code];
    }
}
