<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\CouldNotGeneratePreviewException;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\DefaultImageProviderInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\OtherGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Fetches the binary preview of the image
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ImagePreviewAction
{
    private const THUMBNAIL_FILENAME = 'thumbnail.jpeg';

    /**
     * Why do we need to have this?
     * At first this action was supposed to redirect to the final media. As we now use flysystem to store
     * our thumbnails and the storage is not public. We cannot directly redirect to a public URL.
     * Unfortunatly, liipimagine is designed to directly provide a file URL. So we twisted a bit the system
     * to get the flysystem path instead of a public URL. As liipimagine requires to define a web_root
     * (cannot be empty), we decided to put a dummy root flag (here: vendor/akeneo/pim-community-dev/config/packages/liip_imagine.yml#L8)
     * and remove it here to get the flysystem path.
     */
    private const ROOT_FLAG = '__root__';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var PreviewGeneratorInterface */
    private $previewGenerator;

    /** @var DefaultImageProviderInterface */
    private $defaultImageProvider;

    /** @var LoaderInterface */
    private $imageLoader;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        PreviewGeneratorInterface $previewGenerator,
        DefaultImageProviderInterface $defaultImageProvider,
        LoaderInterface $imageLoader
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->previewGenerator = $previewGenerator;
        $this->defaultImageProvider = $defaultImageProvider;
        $this->imageLoader = $imageLoader;
    }

    public function __invoke(
        Request $request,
        string $attributeIdentifier,
        string $type
    ): Response {
        $data = $request->get('data');
        $regenerate = $request->isMethod('POST');

        try {
            $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString($attributeIdentifier));
            if ($regenerate) {
                $this->previewGenerator->remove($data, $attribute, $type);
            }
            $imagePreview = $this->previewGenerator->generate($data, $attribute, $type);
        } catch (AttributeNotFoundException $e) {
            $imagePreview = $this->defaultImageProvider->getImageUrl(OtherGenerator::DEFAULT_OTHER, $type);
        } catch (CouldNotGeneratePreviewException $e) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $file = $this->imageLoader->find(str_replace(self::ROOT_FLAG, '', $imagePreview));

        $response = new Response($file->getContent());

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            self::THUMBNAIL_FILENAME
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->setMaxAge(3600 * 24 * 365); // One year
        $response->setSharedMaxAge(3600 * 24 * 365); // One year

        return $response;
    }
}
