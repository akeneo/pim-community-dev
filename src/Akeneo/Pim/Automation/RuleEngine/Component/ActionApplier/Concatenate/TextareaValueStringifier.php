<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class TextareaValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    private const SEARCH_REPLACE_FOR_TEXT = [
        "\r\n",
        "\r",
        "\n",
        '<br></p>',
        '</br></p>',
        '</p>',
        '<br/>',
        '<br>',
    ];

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository, array $attributeTypes)
    {
        parent::__construct($attributeTypes);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function stringify(ValueInterface $value, array $options = []): string
    {
        if (!isset($options['target_attribute_code'])) {
            throw new \LogicException('The "target_attribute_code" option key must be provided.');
        }

        $targetAttribute = $this->attributeRepository->findOneByIdentifier($options['target_attribute_code']);
        if (null === $targetAttribute) {
            return '';
        }

        if ($targetAttribute->getType() === AttributeTypes::TEXTAREA) {
            if ($targetAttribute->isWysiwygEnabled()) {
                return str_replace(["\r", "\n"], '<br/>', $value->__toString());
            }

            return html_entity_decode(strip_tags(trim(str_replace('</p>', PHP_EOL, $value->__toString()))));
        }

        return html_entity_decode(strip_tags(trim(
            str_replace(static::SEARCH_REPLACE_FOR_TEXT, ' ', $value->__toString())
        )));
    }
}
