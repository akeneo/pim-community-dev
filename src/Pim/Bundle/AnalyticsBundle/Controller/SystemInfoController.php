<?php

namespace Pim\Bundle\AnalyticsBundle\Controller;

use Akeneo\Component\Analytics\ChainedDataCollector;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * System info controller
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SystemInfoController
{
    /**
     * @param EngineInterface      $templating
     * @param ChainedDataCollector $dataCollector
     */
    public function __construct(EngineInterface $templating, ChainedDataCollector $dataCollector)
    {
        $this->templating    = $templating;
        $this->dataCollector = $dataCollector;
    }

    /**
     * Displays PIM system info
     *
     * @return Response
     */
    public function indexAction()
    {
        $data    = $this->dataCollector->collect('system_info_report');
        $content = $this->templating->render(
            'PimAnalyticsBundle:SystemInfo:index.html.twig',
            ['data' => $data]
        );

        return new Response($content);
    }

    /**
     * Downloads a complete report of PIM system info as a text file
     *
     * @return Response
     */
    public function downloadAction()
    {
        $data    = $this->dataCollector->collect('system_info_advanced_report');
        $content = $this->templating->render(
            'PimAnalyticsBundle:SystemInfo:index.txt.twig',
            ['data' => $data]
        );

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', sprintf(
            '%s; filename="akeneo-pim-system-info_%s.txt"',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            date('Y-m-d_H:i')
        ));

        return $response;
    }
}
