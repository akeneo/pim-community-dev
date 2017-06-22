<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Mass action controller for edit and delete actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionController
{
    /** @var RequestStack $requestStack */
    protected $requestStack;

    /**
     * @var MassActionDispatcher
     */
    protected $massActionDispatcher;

    /**
     * Constructor
     *
     * @param RequestStack         $requestStack
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(RequestStack $requestStack, MassActionDispatcher $massActionDispatcher)
    {
        $this->requestStack = $requestStack;
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * Mass delete action
     *
     * @return JsonResponse
     */
    public function massActionAction()
    {
        $response = $this->massActionDispatcher->dispatch($this->requestStack->getCurrentRequest());

        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
