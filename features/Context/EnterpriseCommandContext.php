<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
}
