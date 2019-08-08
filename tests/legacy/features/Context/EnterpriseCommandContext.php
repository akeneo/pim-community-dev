<?php

namespace Context;

use Akeneo\Asset\Bundle\Command\GenerateMissingVariationFilesCommand;
use Akeneo\Pim\Enrichment\Bundle\Command\GetProductCommand;
use Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\ApproveProposalCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\CreateDraftCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\PublishProductCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\QueryPublishedProductCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command\SendDraftForApprovalCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Negotiation\Exception\InvalidArgument;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * A context for commands
 *
 * @author    Nina Sarradin
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseCommandContext extends CommandContext
{
    use SpinCapableTrait;

    /**
     * @param string $productIdentifier
     *
     * @throws \Exception
     *
     * @Given /^I publish the product "([^"]*)"$/
     */
    public function iPublishTheProduct($productIdentifier)
    {
        $publishedProductManager = $this->getContainer()->get('pimee_workflow.manager.published_product');
        $productRepository = $this->getContainer()->get('pim_catalog.repository.product');

        $product = $productRepository->findOneByIdentifier($productIdentifier);

        if (null === $product) {
            throw new \LogicException(sprintf('Product "%s" not found.', $productIdentifier));
        }
        $publishedProduct = $publishedProductManager->publish($product);

        if (null === $publishedProduct) {
            throw new \LogicException(sprintf('Product "%s" not published.', $productIdentifier));
        }
    }

    /**
     * @throws \Exception
     *
     * @Given /^I generate missing variations(?: for asset (\S+))?$/
     */
    public function iGenerateMissingVariations($assetCode = null)
    {
        $application = new Application();
        $application->add(new GenerateMissingVariationFilesCommand());

        $command = $application->find('pim:asset:generate-missing-variation-files');
        $command->setContainer($this->getContainer());
        $commandTester = new CommandTester($command);

        $commandOptions = ['command' => $command->getName()];

        if (null !== $assetCode) {
            $commandOptions['-a'] = $assetCode;
        }

        $commandResult = $commandTester->execute($commandOptions);

        if (0 !== $commandResult) {
            throw new \Exception(
                sprintf(
                    'An error occured during the execution of the generate variations command : %s',
                    $commandTester->getDisplay()
                )
            );
        }
    }

    /**
     * @param TableNode $tableProducts
     *
     * @throws \Exception
     *
     * @Then /^I should get the following published products?:$/
     */
    public function iShouldGetTheFollowingPublishedProducts(TableNode $tableProducts)
    {
        foreach ($tableProducts->getHash() as $expected) {
            $expectedResult = json_decode($expected['result'], true);

            $originalProduct =
                $this
                    ->getMainContext()
                    ->getSubContext('fixtures')
                    ->getProduct($expected['product']);

            if (null === $originalProduct) {
                throw new \Exception(
                    sprintf(
                        'An error occurred during the retrieval of the original product'
                    )
                );
            }

            if (null === $expectedResult) {
                throw new \Exception(
                    sprintf(
                        'Looks like the expected result is not valid json : %s',
                        $expected['result']
                    )
                );
            }

            $publishedProduct           = $this->getPublishedProduct($originalProduct);
            $normalizedPublishedProduct = $this->getContainer()->get('pim_standard_format_serializer')->normalize(
                $publishedProduct,
                'standard'
            );

            $diff = static::arrayIntersect($normalizedPublishedProduct, $expectedResult);

            Assert::assertEquals(
                $expectedResult,
                $diff
            );
        }
    }

    /**
     * @param TableNode $tableProducts
     *
     * @throws \Exception
     *
     * @Then /^I should get the following proposals?:$/
     */
    public function iShouldGetTheFollowingProposals(TableNode $tableProducts)
    {
        foreach ($tableProducts->getHash() as $expected) {
            $expectedResult = json_decode($expected['result'], true);

            $product =
                $this
                    ->getMainContext()
                    ->getSubContext('fixtures')
                    ->getProduct($expected['product']);

            if (null === $product) {
                throw new \Exception(
                    sprintf(
                        'An error occurred during the retrieval of the original product'
                    )
                );
            }

            if (null === $expectedResult) {
                throw new \Exception(
                    sprintf(
                        'Looks like the expected result is not valid json : %s',
                        $expected['result']
                    )
                );
            }

            $proposal = $this->getProposal($product, $expected['username']);
            if (null === $proposal) {
                throw new \Exception(
                    sprintf(
                        'An error occurred during the retrieval of the draft "%s"',
                        $expected['product']
                    )
                );
            }

            $changes = $this->sanitizeDraftFileChanges($proposal->getChanges());

            Assert::assertEquals($changes, $expectedResult);
        }
    }

    /**
     * @param TableNode $tableProducts
     *
     * @throws \Exception
     *
     * @Then /^I should not get the following proposals?:$/
     */
    public function iShouldNotGetTheFollowingProposals(TableNode $tableProducts)
    {
        foreach ($tableProducts->getHash() as $expected) {
            $product =
                $this
                    ->getMainContext()
                    ->getSubContext('fixtures')
                    ->getProduct($expected['product']);

            if (null === $product) {
                throw new \Exception(
                    sprintf(
                        'An error occurred during the retrieval of the original product'
                    )
                );
            }

            $proposal = $this->getProposal($product, $expected['username']);
            if (null !== $proposal) {
                throw new \Exception(
                    sprintf(
                        'Draft for the product "%s" and the user "%s" should not exists',
                        $expected['product'],
                        $expected['username']
                    )
                );
            }
        }
    }

    /**
     * @param TableNode $filters
     *
     * @Then /^I should get the following published products results for the given filters:$/
     */
    public function iShouldGetTheFollowingPublishedProductsResultsForTheGivenFilters(TableNode $filters)
    {
        $application = new Application();
        $application->add(new QueryPublishedProductCommand());

        $command = $application->find('pim:published-product:query');
        $command->setContainer($this->getMainContext()->getContainer());

        foreach ($filters->getHash() as $filter) {
            $this->spin(function () use ($filter, $command) {
                $commandTester = new CommandTester($command);
                $commandTester->execute(
                    ['command' => $command->getName(), '--json-output' => true, 'json_filters' => $filter['filter']]
                );

                $expected = json_decode($filter['result']);
                $actual   = json_decode($commandTester->getDisplay());

                Assert::assertEquals($expected, $actual);

                return true;
            }, sprintf('Impossible to assert result "%s" for filter "%s"', $filter['result'], $filter['filter']));
        }
    }

    /**
     * @param array $changes
     *
     * @return array
     */
    protected function sanitizeDraftFileChanges(array $changes)
    {
        foreach ($changes['values'] as $attributeCode => $change) {
            foreach ($change as $changeKey => $data) {
                $data = $data['data'];
                if (isset($data['filePath']) && isset($data['originalFilename'])) {
                    $filenameLength = strlen($data['originalFilename']);
                    $data['filePath'] = substr($data['filePath'], -$filenameLength);
                }
                if (isset($data['hash'])) {
                    unset($data['hash']);
                }
                $changes['values'][$attributeCode][$changeKey]['data'] = $data;
            }
        }

        return $changes;
    }

    /**
     * Retrieve published product from original one
     *
     * @param ProductInterface $originalProduct
     *
     * @return ProductInterface
     */
    protected function getPublishedProduct(ProductInterface $originalProduct)
    {
        $repository       = $this->getContainer()->get('pimee_workflow.repository.published_product');
        $publishedProduct = $repository->findOneByOriginalProduct($originalProduct);

        return $publishedProduct;
    }

    /**
     * Retrieve proposal
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return EntityWithValuesDraftInterface|null
     */
    protected function getProposal(ProductInterface $product, $username)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->getProductDraft($product, $username);
    }
}
