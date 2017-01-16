<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PocController
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 */
class PocController
{
    // inject deps
    public function __construct()
    {
        // $this->normalizer = $normalizer
        // bla-bla-bla
    }

    /**
     * Get action
     *
     * @param $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        // fetch object from repository
        return new JsonResponse($this->getModel());
    }

    /**
     * Post action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        return $this->saveModel($data);
    }

    /**
     * Put action
     *
     * @param Request $request
     * @param $code
     *
     * @return JsonResponse
     */
    public function putAction(Request $request, $code)
    {
        $data = json_decode($request->getContent(), true);

        return $this->saveModel($data);
    }

    /**
     * Remove action
     *
     * @param $code
     *
     * @return JsonResponse
     */
    public function removeAction($code)
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Simple select action
     *
     * @return JsonResponse
     */
    public function simpleSelectAction()
    {
        return new JsonResponse($this->getSimpleSelects());
    }

    /**
     * Multi select action
     *
     * @return JsonResponse
     */
    public function multiSelectAction()
    {
        return new JsonResponse($this->getMultiSelects());
    }

    protected function saveModel($object)
    {
        // todo-a2x: validation

        return new JsonResponse($object);
    }

    /**
     * Gets model
     *
     * Assuming our model has
     * - code
     * - label (translations)
     * - simple_select
     * - multi_select
     *
     * @return array
     */
    protected function getModel()
    {
        return [
            'code' => 'poc',
            'new_name' => 'Newname value',
            'labels' => [
                'en_US' => 'Label en',
                'fr_FR' => 'Label fr',
            ],
            'simple_select' => 'ss1',
            'multi_select' => [
                'ms1',
                'ms3',
            ],
            'meta' =>[
                'updated' => '',
                'created' => '',
                'id' => 2,
            ]
        ];
    }

    protected function getSimpleSelects()
    {
        return [
            [
                'code' => 'ss1',
                'labels' => [
                    'en_US' => 'SS1 en',
                    'fr_FR' => 'SS1 fr'
                ],
            ],
            [
                'code' => 'ss2',
                'labels' => [
                    'en_US' => 'SS2 en',
                    'fr_FR' => 'SS2 fr',
                ],
            ],
        ];
    }

    protected function getMultiSelects()
    {
        return [
            [
                'code' => 'ms1',
                'labels' => [
                    'en_US' => 'MS1 en',
                    'fr_FR' => 'MS1 fr'
                ],
            ],
            [
                'code' => 'ms2',
                'labels' => [
                    'en_US' => 'MS2 en',
                    'fr_FR' => 'MS2 fr',
                ],
            ],
            [
                'code' => 'ms3',
                'labels' => [
                    'en_US' => 'MS3 en',
                    'fr_FR' => 'MS3 fr',
                ],
            ],
        ];
    }
}
