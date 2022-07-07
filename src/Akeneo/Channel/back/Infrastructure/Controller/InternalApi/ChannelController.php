<?php

namespace Akeneo\Channel\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindCategoryTrees;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private NormalizerInterface $normalizer,
        private ObjectUpdaterInterface $updater,
        private SaverInterface $saver,
        private RemoverInterface $remover,
        private SimpleFactoryInterface $channelFactory,
        private ValidatorInterface $validator,
        private SecurityFacadeInterface $securityFacade,
        private FindCategoryTrees $findCategoryTrees
    ) {
    }

    /**
     * Lists all channels
     */
    public function indexAction(Request $request): JsonResponse
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
     */
    public function getAction(Request $request, string $identifier): JsonResponse
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
     * Gets Category tree without apply user permission
     * @return JsonResponse
     */
    public function listCategoryTreeAction(): JsonResponse
    {
        $categoryTrees = $this->findCategoryTrees->execute();
        $normalizeCategoryTrees = array_map(fn (CategoryTree $categoryTree) => $categoryTree->normalize(), $categoryTrees);

        return new JsonResponse($normalizeCategoryTrees);
    }

    /**
     * Saves new channel
     */
    public function postAction(Request $request): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_channel_create')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $channel = $this->channelFactory->create();

        return $this->saveChannel($channel, $request);
    }

    /**
     * Updates channel
     */
    public function putAction(Request $request, string $code): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_channel_edit')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $channel = $this->getChannel($code);

        return $this->saveChannel($channel, $request);
    }

    /**
     * Removes channel
     */
    public function removeAction(Request $request, string $code): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_channel_remove')) {
            throw AccessDeniedException::create(
                __CLASS__,
                __METHOD__,
            );
        }

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

    private function getChannel(string $code): ChannelInterface
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

    private function saveChannel(ChannelInterface $channel, Request $request): JsonResponse
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
