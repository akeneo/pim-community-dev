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
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\DefaultImageProviderInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\OtherGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Fetches the binary preview of the image
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ImagePreviewAction
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var PreviewGeneratorInterface */
    private $previewGenerator;

    /** @var DefaultImageProviderInterface */
    private $defaultImageProvider;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        PreviewGeneratorInterface $previewGenerator,
        DefaultImageProviderInterface $defaultImageProvider
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->previewGenerator = $previewGenerator;
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function __invoke(
        Request $request,
        string $attributeIdentifier,
        string $type
    ): Response {
        $data = $request->get('data');

        try {
            $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString($attributeIdentifier));
            $imagePreview = $this->previewGenerator->generate($data, $attribute, $type);
        } catch (AttributeNotFoundException $e) {
            $imagePreview = $this->defaultImageProvider->getImageUrl(OtherGenerator::DEFAULT_OTHER, $type);
        }

        $filePath = sprintf('%s%s', getcwd(), $imagePreview);

        return new BinaryFileResponse($filePath);
    }
}
