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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalUpsertIntegration extends TestCase
{
    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->proposalUpsert = $this->get('akeneo.pim.automation.suggest_data.proposal.create_proposal');
    }

    public function test_it_creates_a_proposal_from_values(): void
    {
        $product = $this->createProduct('foo', 'familyA');

        $suggestedValues = [
            'a_number_integer' => [
                [
                    'data' => '42',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'a_text' => [
                [
                    'data' => 'Some text',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];

        $this->proposalUpsert->process(
            [new ProposalSuggestedData($product->getId(), $suggestedValues)],
            ProposalAuthor::USERNAME
        );

        $draft = $this->getDraft($product, ProposalAuthor::USERNAME);
        Assert::assertInstanceOf(EntityWithValuesDraftInterface::class, $draft);
        Assert::assertSame(EntityWithValuesDraftInterface::READY, $draft->getStatus());
        Assert::assertEquals(['a_number_integer', 'a_text'], array_keys($draft->getChanges()['values']));
    }

    public function test_it_does_not_create_a_proposal_with_suggested_values_identical_to_the_product(): void
    {
        $values = $suggestedValues = [
            'a_text' => [
                [
                    'data' => 'Some text',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
        $product = $this->createProduct(
            'foo',
            'familyA',
            [
                'values' => $values,
            ]
        );

        $this->proposalUpsert->process(
            [new ProposalSuggestedData($product->getId(), $suggestedValues)],
            ProposalAuthor::USERNAME
        );

        Assert::assertNull($this->getDraft($product, ProposalAuthor::USERNAME));
    }

    public function test_it_updates_an_existing_proposal_with_new_values(): void
    {
        $product = $this->createProduct(
            'foo',
            'familyA'
        );
        $firstSuggestedValues = [
            'a_text' => [
                [
                    'data' => 'Some text',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
        $this->createProposal($product, $firstSuggestedValues, ProposalAuthor::USERNAME);
        $draft = $this->getDraft($product, ProposalAuthor::USERNAME);
        Assert::assertInstanceOf(EntityWithValuesDraftInterface::class, $draft);
        $aText = $draft->getValue('a_text', null, null);
        Assert::assertEquals('Some text', $aText);

        $newSuggestedValues = [
            'a_number_integer' => [
                [
                    'data' => '42',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'a_text' => [
                [
                    'data' => 'Some other text',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
        $this->proposalUpsert->process(
            [new ProposalSuggestedData($product->getId(), $newSuggestedValues)],
            ProposalAuthor::USERNAME
        );

        $draft = $this->getDraft($product, ProposalAuthor::USERNAME);
        Assert::assertInstanceOf(EntityWithValuesDraftInterface::class, $draft);
        Assert::assertSame(EntityWithValuesDraftInterface::READY, $draft->getStatus());
        Assert::assertEquals(['a_number_integer', 'a_text'], array_keys($draft->getChanges()['values']));
        $aText = $draft->getValue('a_text', null, null);
        Assert::assertEquals('Some other text', $aText);
    }

    public function test_that_it_does_not_create_proposals_with_invalid_values(): void
    {
        $product = $this->createProduct(
            'foo',
            'familyA'
        );
        $invalidValues = [
            'un_unknown_attribute' => [
                [
                    'data' => '42',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
        $this->proposalUpsert->process(
            [
                new ProposalSuggestedData($product->getId(), $invalidValues),
            ],
            ProposalAuthor::USERNAME
        );

        Assert::assertNull($this->getDraft($product, ProposalAuthor::USERNAME));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     * @param array|null $data
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier, string $familyCode, ?array $data = null): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        if (null !== $data) {
            $this->get('pim_catalog.updater.product')->update($product, $data);
        }
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param EntityWithValuesInterface $product
     * @param array $values
     * @param string $author
     */
    private function createProposal(EntityWithValuesInterface $product, array $values, string $author): void
    {
        $this->get('pimee_workflow.updater.product_without_permission')->update($product, ['values' => $values]);
        $draft = $this->get('pimee_workflow.product.builder.draft')->build($product, $author);
        Assert::assertInstanceOf(EntityWithValuesDraftInterface::class, $draft);
        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $draft->markAsReady();
        $this->get('pimee_workflow.saver.product_draft')->save($draft);
    }

    /**
     * @param ProductInterface $product
     * @param string $author
     *
     * @return EntityWithValuesDraftInterface|null
     */
    private function getDraft(ProductInterface $product, string $author): ?EntityWithValuesDraftInterface
    {
        $productDraftRepository = $this->get('pimee_workflow.repository.product_draft');

        return $productDraftRepository->findUserEntityWithValuesDraft($product, $author);
    }
}
