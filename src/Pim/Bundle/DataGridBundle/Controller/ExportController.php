<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Datagrid controller for export action
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportController
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var DatagridManager $datagridManager */
    protected $datagridManager;

    /** @var MassActionParametersParser $parametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher $massActionDispatcher */
    protected $massActionDispatcher;

    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var ProductManager $productManager */
    protected $productManager;

    /** @var UserContext $userContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param DatagridManager $datagridManager
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher $massActionDispatcher
     * @param SerializerInterface $serializer
     * @param ProductManager $productManager
     * @param UserContext $userContext
     */
    public function __construct(
        Request $request,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer
    ) {
//         $this->container = $container;

//         $this->datagridManager = $datagridManager;

//         $this->productManager = $productManager;
//         $this->userContext          = $userContext;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->serializer           = $serializer;

        $this->request = $request;

    }

    /**
     * Call export action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        // Export time execution depends on entities exported
        ignore_user_abort(false);
        set_time_limit(0);

        return $this->createStreamedResponse($request)->send();
    }

    /**
     * Create a streamed response containing a file
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function createStreamedResponse(Request $request)
    {
        $filename = $this->createFilename();

        $response = new StreamedResponse();
        $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $attachment);
        $response->setCallback($this->quickExportCallback($request));

        return $response;
    }

    /**
     * Create filename
     * @return string
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'export_%s_%s_%s.csv',
            $dateTime->format('Y-m-d_H:i:s')
        );
    }

    /**
     * Callback for streamed response
     * dispatch mass action and returning result as a file
     *
     * @return \Closure
     */
    protected function quickExportCallback(Request $request)
    {
        return function () use ($request) {
            flush();

            $format  = 'csv';
            $context = [
                'withHeader'    => true,
                'heterogeneous' => true
            ];

            $parameters  = $this->parametersParser->parse($request);
            $requestData = array_merge($request->query->all(), $request->request->all());

            $results = $this->massActionDispatcher->dispatch(
                $requestData['gridName'],
                $requestData['actionName'],
                $parameters,
                $requestData
            );

            echo $this->serializer->serialize($results, $format, $context);

            flush();
        };
    }
}
