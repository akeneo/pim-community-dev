<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ImagePreviewUrlGenerator;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItemHydrator;

/**
 * RecordItem Value hydrator for value of type "Url".
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class UrlValueHydrator implements ValueHydratorInterface
{
    /** @var ImagePreviewUrlGenerator */
    private $imagePreviewUrlGenerator;

    public function __construct(ImagePreviewUrlGenerator $imagePreviewUrlGenerator)
    {
        $this->imagePreviewUrlGenerator = $imagePreviewUrlGenerator;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof UrlAttribute;
    }

    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
        $attributeIdentifier = $attribute->getIdentifier()->stringValue();
        $normalizedValue['data'] = $this->imagePreviewUrlGenerator->generate(
            $normalizedValue['data'],
            $attributeIdentifier,
            RecordItemHydrator::THUMBNAIL_PREVIEW_TYPE
        );

        return $normalizedValue;
    }
}
