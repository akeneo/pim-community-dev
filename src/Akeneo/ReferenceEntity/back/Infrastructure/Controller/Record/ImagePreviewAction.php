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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator\PreviewGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fetches the binary preview of the url
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ImagePreviewAction
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var PreviewGeneratorInterface  */
    private $previewGenerator;

    public function __construct(AttributeRepositoryInterface $attributeRepository, PreviewGeneratorInterface $previewGenerator)
    {
        $this->attributeRepository = $attributeRepository;
        $this->previewGenerator = $previewGenerator;
    }

    public function __invoke(
        string $data,
        string $attributeIdentifier,
        string $type
    ): Response {
        /** @var UrlAttribute $attribute */
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString($attributeIdentifier));
        $imagePreview = $this->previewGenerator->generate($data, $attribute, $type);

        return new RedirectResponse($imagePreview, Response::HTTP_MOVED_PERMANENTLY);
    }
}
