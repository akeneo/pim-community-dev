<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Pim\Bundle\DataGridBundle\Extension\MassAction\ProductMassActionDispatcher;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

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

    /** @var MassActionParametersParser $parametersParser */
    protected $parametersParser;

    /** @var ProductMassActionDispatcher $massActionDispatcher */
    protected $massActionDispatcher;

    /**
     * Constructor
     *
     * @param Request                     $request
     * @param MassActionParametersParser  $parametersParser
     * @param ProductMassActionDispatcher $massActionDispatcher
     */
    public function __construct(
        Request $request,
        MassActionParametersParser $parametersParser,
        ProductMassActionDispatcher $massActionDispatcher
    ) {
        $this->request              = $request;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * Mass delete action
     */
    public function massActionAction($gridName, $actionName)
    {
        $parameters = $this->parametersParser->parse($this->request);

        $requestData = array_merge(
            $this->request->query->all(),
            $this->request->request->all()
        );

        $response = $this->massActionDispatcher->dispatch($gridName, $actionName, $parameters, $requestData);

        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
