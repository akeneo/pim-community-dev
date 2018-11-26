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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;

/**
 * @author    Tamara Robichet <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAttribute
{
    /** @var AttributeIdentifier */
    private $identifier;

    /** @var LabelCollection */
    public $labelCollection;

    /** @var string */
    public $type;

    /** @var bool */
    public $localizable;

    /** @var bool */
    public $scopable;

    /** @var int */
    public $maxCharacters;

    /** @var bool */
    public $isTextarea;

    /** @var bool */
    public $isRichTextEditor;

    /** @var string | null */
    public $validationRule;

    /** @var string | null */
    public $validationRegexp;

    public function __construct(
        AttributeIdentifier $identifier,
        LabelCollection $labelCollection,
        string $type,
        bool $localizable,
        bool $scopable,
        array $additionalProperties
    ) {
        $this->identifier = $identifier;
        $this->labelCollection = $labelCollection;
        $this->type = $type;
        $this->localizable = $localizable;
        $this->scopable = $scopable;
        $this->additionalProperties = $additionalProperties;
    }

    // @TODO - move to additional properties ?
    public function normalize(): array
    {
        $commonProperties = [
            'code' => $this->identifier->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'type' => $this->type,
            'localizable' => $this->localizable,
            'scopable' => $this->scopable
        ];

        return array_merge($commonProperties, $this->additionalProperties);
    }
}
