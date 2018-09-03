<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeTypeController
{
    /** @var AttributeTypeRegistry */
    protected $registry;

    public function __construct(AttributeTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Return all attributes types
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse($this->registry->getSortedAliases());
    }
}
