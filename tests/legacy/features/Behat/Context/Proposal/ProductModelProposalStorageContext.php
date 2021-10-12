<?php

declare(strict_types=1);

namespace PimEnterprise\Behat\Context\Proposal;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class ProductModelProposalStorageContext implements Context
{
    private EntityWithValuesDraftRepositoryInterface $proposalRepository;
    private IdentifiableObjectRepositoryInterface $productModelRepository;
    private AttributeColumnInfoExtractor $attributeColumnInfoExtractor;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityWithValuesDraftRepositoryInterface $proposalRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        EntityManagerInterface $entityManager
    ) {
        $this->proposalRepository = $proposalRepository;
        $this->productModelRepository = $productModelRepository;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->entityManager = $entityManager;
    }

    /**
     * @Then the proposal for product model :productModelIdentifier and author :author should be:
     */
    public function thereShouldBeTheFollowingProductProposal(string $productModelIdentifier, string $author, TableNode $properties)
    {
        $product = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
        if (null === $product) {
            throw new \Exception(sprintf('Cannot find product with code "%s"', $productModelIdentifier));
        }

        $proposal = $this->proposalRepository->findUserEntityWithValuesDraft($product, $author);
        $this->entityManager->refresh($proposal);

        if (null === $proposal) {
            throw new \Exception(
                sprintf(
                    'The proposal for product model with code "%s" and author "%s" does not exist',
                    $productModelIdentifier,
                    $author
                )
            );
        }

        foreach ($properties->getHash() as $rawProposal) {
            $this->checkValue($proposal, $rawProposal['field'], $rawProposal['value']);
        }

        Assert::assertCount(
            $proposal->getValues()->count(),
            $properties->getHash(),
            sprintf('You expect %d values in the proposal. %d values have been created', count($properties->getHash()), $proposal->getValues()->count())
        );
    }

    /**
     * @Then there is no proposal for product model :productIdentifier and author :author
     */
    public function thereIsNoProposalForProductModelAndAuthor(string $productModelIdentifier, string $author)
    {
        $proposal = $this->getProductModelProposalByAuthor($productModelIdentifier, $author);

        Assert::assertNull(
            $proposal,
            sprintf('A proposal has been found for product model "%s" and author "%s"', $productModelIdentifier, $author)
        );
    }

    /**
     * @Then there is one proposal for product model :productIdentifier and author :author
     */
    public function thereIsOneProposalForProductModelAndAuthor(string $productModelIdentifier, string $author)
    {
        $proposal = $this->getProductModelProposalByAuthor($productModelIdentifier, $author);

        Assert::assertNotNull(
            $proposal,
            sprintf('Cannot find proposal for product model "%s" and author "%s"', $productModelIdentifier, $author)
        );
    }

    private function checkValue(EntityWithValuesInterface $proposal, string $field, $value): void
    {
        $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($field);

        $attribute = $infos['attribute'];
        if (null === $attribute) {
            throw new \Exception(sprintf('Cannot find the field "%s"', $field));
        }

        $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
        $productValue = $proposal->getValue(
            $attribute->getCode(),
            $infos['locale_code'],
            $infos['scope_code']
        );

        if ('' === $value) {
            Assert::assertEmpty((string) $productValue);
        } elseif ('prices' === $attribute->getBackendType() && null !== $priceCurrency) {
            // $priceCurrency can be null if we want to test all the currencies at the same time
            // in this case, it's a simple string comparison
            // example: 180.00 EUR, 220.00 USD

            $price = $productValue->getPrice($priceCurrency);
            Assert::assertEquals($value, $price->getData());
        } elseif ('date' === $attribute->getBackendType()) {
            Assert::assertEquals($value, $productValue->getData()->format('Y-m-d'));
        } elseif ('media' === $attribute->getBackendType()) {
            Assert::assertEquals($value, $productValue->getData()->getOriginalFilename());
        } else {
            Assert::assertEquals($value, (string) $productValue);
        }
    }

    private function getProductModelProposalByAuthor(string $productModelIdentifier, string $author)
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($productModelIdentifier);
        if (null === $productModel) {
            throw new \Exception(sprintf('Cannot find product model with code "%s"', $productModelIdentifier));
        }

        return $this->proposalRepository->findUserEntityWithValuesDraft($productModel, $author);
    }
}
