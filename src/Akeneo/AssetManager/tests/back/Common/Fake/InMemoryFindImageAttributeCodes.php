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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindImageAttributeCodesInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindImageAttributeCodes implements FindImageAttributeCodesInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributes = $this->attributeRepository->getAttributes();
        $imageAttributeCodes = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof ImageAttribute) {
                $imageAttributeCodes[] = $attribute->getCode();
            }
        }

        return $imageAttributeCodes;
    }
}
