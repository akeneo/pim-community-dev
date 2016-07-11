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
        $this->templating = $templating;
        $this->dataCollector = $dataCollector;
    }

    /**
     * @param string $_format
     *
     * @return Response
     */
    public function indexAction($_format)
    {
        $data = $this->dataCollector->collect('system_info_report');
        $content = $this->templating->render(
            sprintf('PimAnalyticsBundle:SystemInfo:index.%s.twig', $_format),
            ['data' => $data]
        );

        $response = new Response($content);
        if ('txt' === $_format) {
            $response->headers->set('Content-Type', 'text/plain');
            $response->headers->set('Content-Disposition', sprintf(
                '%s; filename="akeneo-pim-system-info_%s.txt"',
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                date('Y-m-d_H:i')
            ));
        }

        return $response;
    }
}
