<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Measures controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasuresRestController
{
    /** @var array */
    protected $measuresConfig;

    /**
     * @param array $measures
     */
    public function __construct(array $measures)
    {
        $this->measuresConfig = $measures['measures_config'];
    }

    /**
     * Get measures configuration
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse($this->measuresConfig);
    }
}
