<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Updater\ChannelUpdater;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var MeasureManager */
    protected $measureManager;

    /** @var ChannelUpdater */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ChannelRepositoryInterface    $channelRepository
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param NormalizerInterface           $normalizer
     * @param MeasureManager                $measureManager
     * @param ChannelUpdater                $updater
     * @param SaverInterface                $saver
     * @param RemoverInterface              $remover
     * @param ValidatorInterface            $validator
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        MeasureManager $measureManager,
        ChannelUpdater $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->normalizer = $normalizer;
        $this->measureManager = $measureManager;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $channels = $this->channelRepository->findAll();

        $normalizedChannels = $this->normalizer->normalize($channels, 'internal_api');

        return new JsonResponse($normalizedChannels);
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $channel = $this->getChannel($identifier);

        return new JsonResponse(
            $this->normalizer->normalize($channel, 'internal_api')
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $channel = new Channel(); /* TODO-a2x: use factory */

        return $this->saveChannel($channel, $request);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function putAction(Request $request, $code)
    {
        $channel = $this->getChannel($code);

        return $this->saveChannel($channel, $request);
    }

    /**
     * @param $code
     *
     * @return JsonResponse
     */
    public function removeAction($code)
    {
        $channel = $this->getChannel($code);
        $this->remover->remove($channel);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return JsonResponse
     */
    public function metricAttributesAction()
    {
        $metricAttributes = $this->attributeRepository->findBy(
            ['attributeType' => AttributeTypes::METRIC]
        );

        $attributesWithConversionUnits = [];

        foreach ($metricAttributes as $attribute) {
            if ($units = $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())) {
                $normalizedAttribute = $this->normalizer->normalize($attribute, 'internal_api');
                $attributesWithConversionUnits[] = $this->setUnits($normalizedAttribute, $units);
            }
        }

        return new JsonResponse($attributesWithConversionUnits);
    }

    /**
     * @param $code
     *
     * @return object
     */
    private function getChannel($code)
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
     * @return JsonResponse
     */
    private function saveChannel($channel, $request)
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

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($channel);

        return new JsonResponse(
            $this->normalizer->normalize(
                $channel,
                'internal_api'
            )
        );
    }

    /**
     * @param array $normalizedAttribute
     * @param array $units
     *
     * @return mixed
     */
    private function setUnits($normalizedAttribute, $units)
    {
        $translator = $this->translator;

        array_map(function ($unit) use ($translator, &$normalizedAttribute) {
            return $normalizedAttribute['units'][$unit] = $translator->trans($unit);
        }, array_keys($units));

        return $normalizedAttribute;
    }
}
