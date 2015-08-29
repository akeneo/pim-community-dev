<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Pim\Bundle\NotificationBundle\Update\DataCollectorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\CatalogBundle\Version;

/**
 * New version controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateController
{
    /** @var $updateServerUrl */
    protected $updateServerUrl;

    /**
     * @param string                $updateServerUrl
     */
    public function __construct($updateServerUrl, DataCollectorInterface $collector)
    {
        $this->updateServerUrl = $updateServerUrl;
        $this->collector       = $collector;
    }

    /**
     * Collects data required to fetch new version
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function collectDataAction(Request $request)
    {
        $data = $this->collector->collect();

        $minorVersion = Version::getMinor();
        $minorVersionKey = sprintf('%s-%s', Version::EDITION, $minorVersion);
        $queryParams = http_build_query($data);
        $updateUrl = sprintf('%s/%s?%s', $this->updateServerUrl, $minorVersionKey, $queryParams);

        $response = [
            'update_url' => $updateUrl
        ];

        return new JsonResponse($response);
    }
}
