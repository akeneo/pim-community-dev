<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Sequential edit action controller for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditController
{
    /** @var RouterInterface */
    protected $router;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var SequentialEditManager */
    protected $seqEditManager;

    /** @var UserContext */
    protected $userContext;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SaverInterface */
    protected $saver;

    /** @var array */
    protected $objects;

    /**
     * Constructor
     *
     * @param RouterInterface       $router
     * @param MassActionDispatcher  $massActionDispatcher
     * @param SequentialEditManager $seqEditManager
     * @param UserContext           $userContext
     * @param NormalizerInterface   $normalizer
     * @param SaverInterface        $saver
     */
    public function __construct(
        RouterInterface $router,
        MassActionDispatcher $massActionDispatcher,
        SequentialEditManager $seqEditManager,
        UserContext $userContext,
        NormalizerInterface $normalizer,
        SaverInterface $saver
    ) {
        $this->router = $router;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->seqEditManager = $seqEditManager;
        $this->userContext = $userContext;
        $this->normalizer = $normalizer;
        $this->saver = $saver;
    }

    /**
     * Action for product sequential edition
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sequentialEditAction(Request $request)
    {
        if ($this->seqEditManager->findByUser($this->userContext->getUser())) {
            return new JsonResponse(
                [
                    'route'  => 'pim_enrich_product_index',
                    'params' => ['dataLocale' => $request->get('dataLocale')]
                ]
            );
        }

        $sequentialEdit = $this->seqEditManager->createEntity(
            $this->getObjects($request),
            $this->userContext->getUser()
        );

        $this->saver->save($sequentialEdit);

        return new JsonResponse(
            [
                'route'  => 'pim_enrich_product_edit',
                'params' => [
                    'dataLocale' => $request->get('dataLocale'),
                    'id'         => current($sequentialEdit->getObjectSet())
                ]
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $sequentialEdit = $this->seqEditManager->findByUser($this->userContext->getUser());

        return new JsonResponse($this->normalizer->normalize($sequentialEdit, 'internal_api'));
    }

    /**
     * Get products to mass edit
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getObjects(Request $request)
    {
        if ($this->objects === null) {
            $this->dispatchMassAction($request);
        }

        return $this->objects;
    }

    /**
     * Dispatch mass action
     *
     * @param Request $request
     */
    protected function dispatchMassAction(Request $request)
    {
        $this->objects = $this->massActionDispatcher->dispatch($request);
    }
}
