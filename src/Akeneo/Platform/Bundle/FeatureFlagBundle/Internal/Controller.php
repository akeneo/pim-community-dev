<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

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
    /** @var Registry */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function isEnabledAction()
    {
        return new JsonResponse($this->registry->all());
    }
}
