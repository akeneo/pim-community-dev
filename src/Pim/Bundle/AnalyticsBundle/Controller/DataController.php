<?php

namespace Pim\Bundle\AnalyticsBundle\Controller;

use Akeneo\Component\Analytics\DataCollectorInterface;
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
    /** @var DataCollectorInterface */
    protected $dataCollector;

    /**
     * @param DataCollectorInterface $dataCollector
     */
    public function __construct(DataCollectorInterface $dataCollector)
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
        $data = $this->dataCollector->collect();

        return new JsonResponse($data);
    }
}
