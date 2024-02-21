<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Controller;

use Akeneo\Tool\Component\Analytics\ChainedDataCollector;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Data controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataController
{
    /** @var ChainedDataCollector */
    protected $dataCollector;

    /**
     * @param ChainedDataCollector $dataCollector
     */
    public function __construct(ChainedDataCollector $dataCollector)
    {
        $this->dataCollector = $dataCollector;
    }

    /**
     * Return the collected data
     *
     * @return JsonResponse
     */
    public function collectAction()
    {
        $data = $this->dataCollector->collect('update_checker');

        return new JsonResponse($data);
    }
}
