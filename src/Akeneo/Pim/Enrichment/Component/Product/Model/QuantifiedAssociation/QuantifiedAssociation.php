<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

class QuantifiedAssociation
{
    /**
     * @var string
     */
    private $associationType;

    /**
     * @var string
     */
    private $quantifiedLinksType;

    /**
     * @var array
     */
    private $quantifiedLinks;

    public function __construct(
        string $associationType,
        string $quantifiedLinksType,
        array $quantifiedLinks
    ) {
        $this->associationType = $associationType;
        $this->quantifiedLinksType = $quantifiedLinksType;
        $this->quantifiedLinks = $quantifiedLinks;
    }

    public static function createFromNormalized($associationType, $quantifiedLinksType, array $quantifiedLinksNormalized): self
    {
        $quantifiedLinks = [];
        foreach ($quantifiedLinksNormalized as $quantifiedLinkNormalized) {
            Assert::isArray($quantifiedLinkNormalized);

            $quantifiedLinks[] = QuantifiedLink::createFromNormalize($quantifiedLinkNormalized);
        }

        return new self($associationType, $quantifiedLinksType, $quantifiedLinks);
    }

    public static function createFromMapping(
        array $rawQuantifiedAssociation,
        $associationType,
        $quantifiedLinksType,
        IdMapping $mappedIds
    ): self {
        $quantifiedLinks = [];
        foreach ($rawQuantifiedAssociation as $rawQuantifiedLink) {
            Assert::keyExists($rawQuantifiedLink, 'id');
            Assert::keyExists($rawQuantifiedLink, 'quantity');

            if ($mappedIds->hasIdentifier($rawQuantifiedLink['id'])) {
                $quantifiedLinks[] = new QuantifiedLink(
                    $mappedIds->getIdentifier($rawQuantifiedLink['id']),
                    $rawQuantifiedLink['quantity']
                );
            }
        }

        return new self($associationType, $quantifiedLinksType, $quantifiedLinks);
    }

    public function getProductIdentifiers(): array
    {
        if ($this->quantifiedLinksType !== QuantifiedAssociationCollection::PRODUCTS_QUANTIFIED_LINKS_KEY) {
            return [];
        }

        return array_map(function (QuantifiedLink $quantifiedLink) {
            return $quantifiedLink->identifier();
        }, $this->quantifiedLinks);
    }

    public function getProductModelCodes(): array
    {
        if ($this->quantifiedLinksType !== QuantifiedAssociationCollection::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY) {
            return [];
        }

        return array_map(function (QuantifiedLink $quantifiedLink) {
            return $quantifiedLink->identifier();
        }, $this->quantifiedLinks);
    }

    public function filterProductIdentifiers(array $productIdentifiersToKeep): self
    {
        if (!$this->isProductLinkType()) {
            return $this;
        }

        $quantifiedLinkFiltered = array_filter($this->quantifiedLinks, function (QuantifiedLink $quantifiedLink) use ($productIdentifiersToKeep) {
            return in_array($quantifiedLink->identifier(), $productIdentifiersToKeep);
        });

        return new self($this->associationType, $this->quantifiedLinksType, $quantifiedLinkFiltered);
    }

    public function isProductLinkType(): bool
    {
        return $this->quantifiedLinksType === QuantifiedAssociationCollection::PRODUCTS_QUANTIFIED_LINKS_KEY;
    }

    public function isProductModelLinkType(): bool
    {
        return $this->quantifiedLinksType === QuantifiedAssociationCollection::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY;
    }

    public function filterProductModelCodes(array $productModelCodesToKeep): self
    {
        if (!$this->isProductModelLinkType()) {
            return $this;
        }

        $quantifiedLinkFiltered = array_filter($this->quantifiedLinks, function (QuantifiedLink $quantifiedLink) use ($productModelCodesToKeep) {
            return in_array($quantifiedLink->identifier(), $productModelCodesToKeep);
        });

        return new self($this->associationType, $this->quantifiedLinksType, $quantifiedLinkFiltered);
    }

    public function normalizeWithMapping(IdMapping $mappedId): array
    {
        $result = [
            $this->associationType => [
                $this->quantifiedLinksType => [],
            ],
        ];

        foreach ($this->quantifiedLinks as $quantifiedLink) {
            $quantifiedLinkNormalized = $quantifiedLink->normalize();
            $result[$this->associationType][$this->quantifiedLinksType][] = [
                'id' =>  $mappedId->getId($quantifiedLinkNormalized['identifier']),
                'quantity' =>  $quantifiedLinkNormalized['quantity'],
            ];
        }

        return $result;
    }

    public function getAssociationTypeCode()
    {
        return $this->associationType;
    }

    public function normalize(): array
    {
        $quantifiedLinksNormalized = array_map(function (QuantifiedLink $quantifiedLink) {
            return $quantifiedLink->normalize();
        }, $this->quantifiedLinks);

        return [
            $this->associationType => [
                $this->quantifiedLinksType => $quantifiedLinksNormalized,
            ]
        ];
    }
}
