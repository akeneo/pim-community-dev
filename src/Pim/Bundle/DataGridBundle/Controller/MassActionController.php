<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var MassActionParametersParser */
    protected $parameterParser;

    /**
     * Constructor
     *
     * @param MassActionDispatcher       $massActionDispatcher
     * @param MassActionParametersParser $parameterParser
     */
    public function __construct(
        MassActionDispatcher $massActionDispatcher,
        MassActionParametersParser $parameterParser
    ) {
        $this->massActionDispatcher = $massActionDispatcher;
        $this->parameterParser      = $parameterParser;
    }

    /**
     * Mass delete action
     *
     * @return JsonResponse
     */
    public function massActionAction(Request $request)
    {
        $parameters = $this->parameterParser->parse($request);
        $response = $this->massActionDispatcher->dispatch($parameters);
        $data = [
            'successful' => $response->isSuccessful(),
            'message'    => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}
