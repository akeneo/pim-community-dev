<?php

namespace Akeneo\Channel\Bundle\Controller\InternalApi;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Updater\ChannelUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Channel controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChannelUpdater */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var SimpleFactoryInterface  */
    protected $channelFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param NormalizerInterface        $normalizer
     * @param ObjectUpdaterInterface     $updater
     * @param SaverInterface             $saver
     * @param RemoverInterface           $remover
     * @param SimpleFactoryInterface     $channelFactory
     * @param ValidatorInterface         $validator
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        SimpleFactoryInterface $channelFactory,
        ValidatorInterface $validator
    ) {
        $this->channelRepository = $channelRepository;
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->channelFactory = $channelFactory;
        $this->validator = $validator;
    }

    /**
     * Lists all channels
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $channels = $this->channelRepository->findAll();
        $filterLocales = $request->query->getBoolean('filter_locales', true);

        $normalizedChannels = $this->normalizer->normalize(
            $channels,
            'internal_api',
            ['filter_locales' => $filterLocales]
        );

        return new JsonResponse($normalizedChannels);
    }

    /**
     * Gets channel by code value
     *
     * @param string $identifier
     *
     * @throws HttpExceptionInterface
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $identifier)
    {
        $channel = $this->getChannel($identifier);
        $filterLocales = $request->query->getBoolean('filter_locales', true);

        return new JsonResponse(
            $this->normalizer->normalize(
                $channel,
                'internal_api',
                ['filter_locales' => $filterLocales]
            )
        );
    }

    /**
     * Saves new channel
     *
     * @AclAncestor("pim_enrich_channel_create")
     *
     * @param Request $request
     *
     * @throws PropertyException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $channel = $this->channelFactory->create();

        return $this->saveChannel($channel, $request);
    }

    /**
     * Updates channel
     *
     * @AclAncestor("pim_enrich_channel_edit")
     *
     * @param Request $request
     * @param string  $code
     *
     * @throws HttpExceptionInterface
     * @throws PropertyException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function putAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $channel = $this->getChannel($code);

        return $this->saveChannel($channel, $request);
    }

    /**
     * Removes channel
     *
     * @AclAncestor("pim_enrich_channel_remove")
     *
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     * @throws HttpExceptionInterface
     */
    public function removeAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $channel = $this->getChannel($code);

        try {
            $this->remover->remove($channel);
        } catch (\LogicException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $code
     *
     * @throws HttpExceptionInterface
     *
     * @return ChannelInterface
     */
    protected function getChannel($code)
    {
        $channel = $this->channelRepository->findOneBy(
            [
                'code' => $code,
            ]
        );

        if (null === $channel) {
            throw new NotFoundHttpException(
                sprintf('Channel with code %s does not exist.', $code)
            );
        }

        return $channel;
    }

    /**
     * @param ChannelInterface $channel
     * @param Request          $request
     *
     * @throws \LogicException
     * @throws PropertyException
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    protected function saveChannel($channel, $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->updater->update($channel, $data);

        $violations = $this->validator->validate($channel);

        if (0 < $violations->count()) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage()
                ];
            }

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($channel);

        return new JsonResponse(
            $this->normalizer->normalize(
                $channel,
                'internal_api',
                ['filter_locales' => false]
            )
        );
    }
}
