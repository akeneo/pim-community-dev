<?php

namespace Oro\Bundle\PimDataGridBundle\Controller;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export\ExportMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Datagrid controller for export action
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportController
{
    /** @var RequestStack $requestStack */
    protected $requestStack;

    /** @var MassActionDispatcher $massActionDispatcher */
    protected $massActionDispatcher;

    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var ExportMassAction $exportMassAction */
    protected $exportMassAction;

    /**
     * @param RequestStack         $requestStack
     * @param MassActionDispatcher $massActionDispatcher
     * @param SerializerInterface  $serializer
     */
    public function __construct(
        RequestStack $requestStack,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer
    ) {
        $this->requestStack = $requestStack;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->serializer = $serializer;
    }

    /**
     * Data export action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // Export time execution depends on entities exported
        ignore_user_abort(false);
        set_time_limit(0);

        return $this->createStreamedResponse()->send();
    }

    /**
     * Create a streamed response containing a file
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function createStreamedResponse()
    {
        $filename = $this->createFilename();

        $response = new StreamedResponse();
        $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Type', $this->getContentType());
        $response->headers->set('Content-Disposition', $attachment);
        $response->setCallback($this->quickExportCallback());

        return $response;
    }

    /**
     * Create filename
     *
     * @return string
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'export_%s.%s',
            $dateTime->format('Y-m-d_H-i-s'),
            $this->getFormat()
        );
    }

    /**
     * Callback for streamed response
     * Dispatch mass action and returning result as a file
     *
     * @return \Closure
     */
    protected function quickExportCallback()
    {
        return function () {
            flush();

            $this->quickExport();

            flush();
        };
    }

    /**
     * Launch quick export dispatching action and serialize results
     */
    protected function quickExport()
    {
        $results = $this->massActionDispatcher->dispatch($this->requestStack->getCurrentRequest());

        echo $this->serializer->serialize($results, $this->getFormat(), $this->getContext());
    }

    /**
     * Get asked content type for streamed response
     *
     * @return string
     */
    protected function getContentType()
    {
        return $this->requestStack->getCurrentRequest()->get('_contentType');
    }

    /**
     * Get asked format type for exported file
     *
     * @return string
     */
    protected function getFormat()
    {
        return $this->requestStack->getCurrentRequest()->get('_format');
    }

    /**
     * Get context for serializer
     *
     * @return array
     */
    protected function getContext()
    {
        return $this->getExportMassAction()->getExportContext();
    }

    /**
     * TODO: Get from datagrid builder ?
     *
     * @return \Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export\ExportMassAction
     */
    protected function getExportMassAction()
    {
        if ($this->exportMassAction === null) {
            $this->exportMassAction = $this->massActionDispatcher->getMassActionByNames(
                $this->requestStack->getCurrentRequest()->get('actionName'),
                $this->requestStack->getCurrentRequest()->get('gridName')
            );
        }

        return $this->exportMassAction;
    }
}
