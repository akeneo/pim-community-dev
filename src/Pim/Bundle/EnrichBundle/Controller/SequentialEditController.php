<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
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

    /** @var MassActionParametersParser */
    protected $parameterParser;

    /**
     * Constructor
     *
     * @param RouterInterface            $router
     * @param MassActionDispatcher       $massActionDispatcher
     * @param SequentialEditManager      $seqEditManager
     * @param UserContext                $userContext
     * @param NormalizerInterface        $normalizer
     * @param SaverInterface             $saver
     * @param MassActionParametersParser $parameterParser
     */
    public function __construct(
        RouterInterface $router,
        MassActionDispatcher $massActionDispatcher,
        SequentialEditManager $seqEditManager,
        UserContext $userContext,
        NormalizerInterface $normalizer,
        SaverInterface $saver,
        MassActionParametersParser $parameterParser
    ) {
        $this->router               = $router;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->seqEditManager       = $seqEditManager;
        $this->userContext          = $userContext;
        $this->normalizer           = $normalizer;
        $this->saver                = $saver;
        $this->parameterParser      = $parameterParser;
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

        $parameters = $this->parameterParser->parse($request);

        # PIM-6360: to refactor for the product models sequential edit to work
        $filteredIds = [];
        foreach ($parameters['values'] as $id) {
            if (1 !== preg_match('/^product_model_/', $id)) {
                $filteredIds[] = $id;
            }
        }
        $parameters['values'] = $filteredIds;

        $sequentialEdit = $this->seqEditManager->createEntity(
            $this->massActionDispatcher->dispatch($parameters),
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
     * @return JsonResponse
     */
    public function getAction()
    {
        $sequentialEdit = $this->seqEditManager->findByUser($this->userContext->getUser());

        return new JsonResponse($this->normalizer->normalize($sequentialEdit, 'internal_api'));
    }

    /**
     * @return JsonResponse
     */
    public function removeAction()
    {
        $this->seqEditManager->removeByUser($this->userContext->getUser());

        return new JsonResponse();
    }
}
