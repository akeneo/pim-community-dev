<?php

namespace Context;

use Akeneo\Component\Console\CommandResult;
use Behat\Gherkin\Node\TableNode;
use Pim\Bundle\CatalogBundle\Command\GetProductCommand;
use Pim\Bundle\CatalogBundle\Command\UpdateProductCommand;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateMissingVariationFilesCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\ApproveProposalCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\CreateDraftCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\PublishProductCommand;
use PimEnterprise\Bundle\WorkflowBundle\Command\SendDraftForApprovalCommand;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
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
                        'An error occurred during the retrieval of the draft "%s"',
                        $expected['product']
                    )
                );
            }

            $changes = $this->sanitizeDraftFileChanges($proposal->getChanges());

            assertEquals($changes, $expectedResult);
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

        $result         = trim($proposalTester->getDisplay());
        $expectedResult = sprintf('Proposal "%s" has been approved', $product);

        if ('' === $not && $result !== $expectedResult) {
            throw new \Exception($result);
        } elseif ('' !== $not && $result === $expectedResult) {
            throw new \Exception($result);
        }
    }

    /**
     * @param string $product
     * @param string $username
     *
     * @throws \Exception
     *
     * @Given /^I send draft "([^"]+)" created by "([^"]+)" for approval"$/
     */
    public function iSendDraftForApproval($product, $username)
    {
        $application = new Application();
        $application->add(new SendDraftForApprovalCommand());

        $proposal = $application->find('pim:draft:send-for-approval');
        $proposal->setContainer($this->getContainer());
        $proposalTester = new CommandTester($proposal);

        $proposalTester->execute(
            [
                'command'    => $proposal->getName(),
                'identifier' => $product,
                'username'   => $username,
            ]
        );
    }

    /**
     * @Then /^I should get the following product drafts after apply the following updater to it:$/
     *
     * @param TableNode $updates
     *
     * @throws \Exception
     */
    public function iShouldGetTheFollowingProductDraftsAfterApplyTheFollowingUpdaterToIt(TableNode $updates)
    {
        $application = $this->getApplicationsForUpdaterProduct();

        $draftCommand = $application->find('pim:draft:create');
        $draftCommand->setContainer($this->getMainContext()->getContainer());
        $draftCommandTester = new CommandTester($draftCommand);

        $getCommand = $application->find('pim:product:get');
        $getCommand->setContainer($this->getMainContext()->getContainer());
        $getCommandTester = new CommandTester($getCommand);

        foreach ($updates->getHash() as $update) {
            $username = isset($update['username']) ? $update['username'] : null;

            $draftCommandTester->execute(
                [
                    'command'      => $draftCommand->getName(),
                    'identifier'   => $update['product'],
                    'json_updates' => $update['actions'],
                    'username'     => $username
                ]
            );

            $expected = json_decode($update['result'], true);
            if (isset($expected['product'])) {
                $getCommandTester->execute(
                    [
                        'command'    => $getCommand->getName(),
                        'identifier' => $expected['product']
                    ]
                );
                unset($expected['product']);
            } else {
                $getCommandTester->execute(
                    [
                        'command'    => $getCommand->getName(),
                        'identifier' => $update['product']
                    ]
                );
            }

            $actual = json_decode($getCommandTester->getDisplay(), true);

            if (null === $actual) {
                throw new \Exception(sprintf(
                    'An error occurred during the execution of the update command : %s',
                    $getCommandTester->getDisplay()
                ));
            }

            if (null === $expected) {
                throw new \Exception(sprintf(
                    'Looks like the expected result is not valid json : %s',
                    $update['result']
                ));
            }
            $diff = $this->arrayIntersect($actual, $expected);

            assertEquals(
                $expected,
                $diff
            );
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
     * @return Application
     */
    protected function getApplicationsForUpdaterProduct()
    {
        $application = new Application();
        $application->add(new UpdateProductCommand());
        $application->add(new CreateDraftCommand());
        $application->add(new SendDraftForApprovalCommand());
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
     * @return ProductDraftInterface|null
     */
    protected function getProposal(ProductInterface $product, $username)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->getProductDraft($product, $username);
    }
}
