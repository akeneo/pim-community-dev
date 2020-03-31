<?php

namespace Akeneo\Platform\Bundle\UIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class FeatureFlagController
{
    public function isEnabledAction($feature)
    {
        if (false === strpos($feature, 'FLAG_')) {
            throw new \Exception('The feature should start by "FLAG_".');
        }

        $isEnabled = false;
        if (isset($_ENV[$feature]) && true === boolval($_ENV[$feature])) {
            $isEnabled = true;
        }

        return new JsonResponse([$feature => $isEnabled]);
    }
}
