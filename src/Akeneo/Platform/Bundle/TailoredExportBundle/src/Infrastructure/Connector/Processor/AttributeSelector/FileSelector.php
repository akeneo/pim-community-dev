<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\MediaExporterPathGenerator;

class FileSelector implements AttributeSelectorInterface
{
    /** @var string[] */
    private array $supportedAttributeTypes;

    public function __construct(
        array $supportedAttributeTypes
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    public function applySelection(array $selectionConfiguration, $entity, Attribute $attribute, ValueInterface $value): string
    {
        if (!$entity instanceof ProductInterface && !$entity instanceof ProductModelInterface) {
            throw new \LogicException('Cannot apply File selection on this entity');
        }

        $data = $value->getData();

        if (null === $data) {
            return '';
        }

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::KEY:
                return $data->getKey();
            case SelectionTypes::NAME:
                return $data->getOriginalFilename();
            case SelectionTypes::PATH:
                $identifier = $entity instanceof ProductInterface ? $entity->getIdentifier() : $entity->getCode();
                $exportDirectory = MediaExporterPathGenerator::generate(
                    $identifier,
                    $attribute->code(),
                    $value->getScopeCode(),
                    $value->getLocaleCode()
                );

                return sprintf('%s%s', $exportDirectory, $data->getOriginalFilename());
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, Attribute $attribute): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::KEY, SelectionTypes::PATH, SelectionTypes::NAME])
            && in_array($attribute->type(), $this->supportedAttributeTypes);
    }
}
