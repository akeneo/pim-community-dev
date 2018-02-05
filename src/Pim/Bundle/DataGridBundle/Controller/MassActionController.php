<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mass action controller for edit and delete actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionController
{
    /** @var Request $request */
    protected $request;

    /**
     * @var MassActionDispatcher
     */
    protected $massActionDispatcher;

    /**
     * Constructor
     *
     * @param Request              $request
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(Request $request, MassActionDispatcher $massActionDispatcher)
    {
        $this->request = $request;
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * Mass delete action
     *
     * @return RedirectResponse|JsonResponse
     */
    public function massActionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $response = $this->massActionDispatcher->dispatch($this->request);

        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
