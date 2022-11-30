<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\CouldNotGeneratePreviewException;
use Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator\PreviewGeneratorInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Fetches the binary preview of the image.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImagePreviewController
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

    public function __construct(
        private GetAttribute $getAttribute,
        private PreviewGeneratorInterface $previewGenerator,
        private LoaderInterface $imageLoader,
    ) {
    }

    public function __invoke(
        Request $request,
        string $attributeCode,
        string $type,
    ): Response {
        $data = $request->get('data');
        if (null === $data) {
            return new JsonResponse('Data is required', Response::HTTP_BAD_REQUEST);
        }

        $data = urldecode($data);
        $regenerate = $request->isMethod('POST');

        try {
            $attribute = $this->getAttribute->byCode(new AttributeCode($attributeCode));
            if ($regenerate) {
                $this->previewGenerator->remove($data, $attribute, $type);
            }
            $imagePreview = $this->previewGenerator->generate($data, $attribute, $type);
        } catch (CouldNotGeneratePreviewException) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $file = $this->imageLoader->find(str_replace(self::ROOT_FLAG, '', $imagePreview));

        $response = new Response($file->getContent());

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            self::THUMBNAIL_FILENAME,
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->setMaxAge(3600 * 24 * 365); // One year
        $response->setSharedMaxAge(3600 * 24 * 365); // One year

        return $response;
    }
}
