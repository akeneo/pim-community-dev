<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class UrlAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'url';

    /** @var AttributeUrlPrefix  */
    private $urlPrefix;

    /** @var AttributeUrlSuffix  */
    private $urlSuffix;

    /** @var AttributeUrlType  */
    private $urlType;

    private function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeUrlPrefix $urlPrefix,
        AttributeUrlSuffix $urlSuffix,
        AttributeUrlType $urlType
    ) {
        parent::__construct(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->urlPrefix = $urlPrefix;
        $this->urlSuffix = $urlSuffix;
        $this->urlType = $urlType;
    }

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeUrlPrefix $urlPrefix,
        AttributeUrlSuffix $urlSuffix,
        AttributeUrlType $urlType
    ) {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $urlPrefix,
            $urlSuffix,
            $urlType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'url_type' => $this->urlType->normalize(),
                'prefix' => $this->urlPrefix->normalize(),
                'suffix' => $this->urlSuffix->normalize(),
            ]
        );
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
