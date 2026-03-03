<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Backend route for frontend feature flag system
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Controller
{
    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    public function isEnabledAction()
    {
        return new JsonResponse($this->featureFlags->all());
    }
}
