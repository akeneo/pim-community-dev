<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

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
     * @var MassActionDispatcher $massActionDispatcher
     */
    protected $massActionDispatcher;

    /**
     * Constructor
     *
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(Request $request, MassActionDispatcher $massActionDispatcher)
    {
        $this->request              = $request;
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * Mass delete action
     */
    public function massActionAction()
    {
        $response = $this->massActionDispatcher->dispatch($this->request);

        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
