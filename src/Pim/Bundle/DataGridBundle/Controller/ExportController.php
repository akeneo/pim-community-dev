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
        ContainerInterface $container,
        DatagridManager $datagridManager,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        UserContext $userContext
    ) {
        $this->container = $container;

        $this->datagridManager = $datagridManager;

        $this->productManager = $productManager;
        $this->userContext          = $userContext;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->serializer           = $serializer;

        $this->productManager->setLocale($this->getDataLocale());
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

        // TODO: Move in quickExportCallback method
        $parameters  = $this->parametersParser->parse($request);
        $requestData = array_merge($request->query->all(), $request->request->all());

        $qb = $this->massActionDispatcher->dispatch(
            $requestData['gridName'],
            $requestData['actionName'],
            $parameters,
            $requestData
        );
        // --END TODO --

        // TODO: Move in FileNameBuilder or method
        $dateTime = new \DateTime();
        $fileName = sprintf(
            'products_export_%s_%s_%s.csv',
            $this->getDataLocale(),
            $this->productManager->getScope(),
            $dateTime->format('Y-m-d_H:i:s')
        );

        // prepare response
        $response = new StreamedResponse();
        $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $attachment);
        $response->setCallback($this->quickExportCallback($qb));

        return $response->send();
    }

    /**
     * Quick export callback
     *
     * @param QueryBuilder $qb
     *
     * @return \Closure
     */
    protected function quickExportCallback(QueryBuilder $qb)
    {
        return function () use ($qb) {
            flush();

            $format  = 'csv';
            $context = [
                'withHeader'    => true,
                'heterogeneous' => true
            ];

            $rootAlias = $qb->getRootAlias();
            $qb->resetDQLPart('select');
            $qb->resetDQLPart('from');
            $qb->select($rootAlias);
            $qb->from($this->productManager->getFlexibleName(), $rootAlias);

            $results = $qb->getQuery()->execute();
            echo $this->serializer->serialize($results, $format, $context);

            flush();
        };
    }

    /**
     * Get data locale code
     *
     * @return string
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
