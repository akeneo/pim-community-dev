<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Pim\Bundle\CatalogBundle\Command\GetProductCommand;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Command\UpdateProductCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\ApproveProposalCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\PublishProductCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * A context for commands
 *
 * @author    Nina Sarradin
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseCommandContext extends CommandContext
{
    /**
     * @param string $product
     *
     * @throws \Exception
     *
     * @Given /^I publish the product "([^"]*)"$/
     */
    public function iPublishTheProduct($product)
    {
        $application = new Application();
        $application->add(new PublishProductCommand());

        $publishCommand = $application->find('pim:product:publish');
        $publishCommand->setContainer($this->getContainer());
        $publishCommandTester = new CommandTester($publishCommand);

        $publishCommandTester->execute(
            [
                'command'    => $publishCommand->getName(),
                'identifier' => $product
            ]
        );

        $result = json_decode($publishCommandTester->getDisplay());

        if (0 != $result) {
            throw new \Exception(
                sprintf(
                    'An error occured during the execution of the publish command : %s',
                    $publishCommandTester->getDisplay()
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

            $publishedProduct = $this->getPublishedProduct($originalProduct);
            $normalizedPublishedProduct = $this->getContainer()->get('pim_serializer')->normalize(
                $publishedProduct,
                'json'
            );

            $diff = static::arrayIntersect($normalizedPublishedProduct, $expectedResult);

            assertEquals(
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
                        'An error occurred during the retrieval of the draft "%s"', $expected['product']
                    )
                );
            }
            assertEquals($proposal->getChanges(), $expectedResult);
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
     * @param string $not
     * @param string $product
     * @param string $username
     *
     * @throws \Exception
     *
     * @Given /^I( failed to)? approve the proposal of the product "([^"]*)" created by user "([^"]*)"$/
     */
    public function iApproveTheProposal($not, $product, $username)
    {
        $application = new Application();
        $application->add(new ApproveProposalCommand());

        $proposal = $application->find('pim:proposal:approve');
        $proposal->setContainer($this->getContainer());
        $proposalTester = new CommandTester($proposal);

        $proposalTester->execute(
            [
                'command'    => $proposal->getName(),
                'identifier' => $product,
                'username'   => $username,
            ]
        );

        $result = trim($proposalTester->getDisplay());
        $expectedResult = sprintf('Proposal "%s" has been approved', $product);

        if ('' === $not && $result !== $expectedResult) {
            throw new \Exception($result);
        } elseif ('' !== $not && $result === $expectedResult) {
            throw new \Exception($result);
        }
    }

    /**
     * @param TableNode $updates
     *
     * @throws \Exception
     */
    public function iShouldGetTheFollowingProductsAfterApplyTheFollowingUpdaterToIt(TableNode $updates)
    {
        parent::iShouldGetTheFollowingProductsAfterApplyTheFollowingUpdaterToIt($updates);
    }

    /**
     * @return Application
     */
    protected function getApplicationsForUpdaterProduct()
    {
        $application = new Application();
        $application->add(new UpdateProductCommand());
        $application->add(new GetProductCommand());

        return $application;
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
        $repository = $this->getContainer()->get('pimee_workflow.repository.published_product');
        $publishedProduct = $repository->findOneByOriginalProduct($originalProduct);

        return $publishedProduct;
    }

    /**
     * Retrieve proposal
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft
     */
    protected function getProposal(ProductInterface $product, $username)
    {
        $repository = $this->getContainer()->get('pimee_workflow.repository.product_draft');

        return $repository->findUserProductDraft($product, $username);
    }
}
