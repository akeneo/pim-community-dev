<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Validate a product, its fields and values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValidatorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:debug:product-validator')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product = $this->getProduct($identifier);
        if (!$product) {
            $output->writeln(sprintf('<error>Product with identifier "%s" not found<error>', $identifier));
            return;
        }

        // update to force errors
        // TODO : test different constraint on different attribute type
        // TODO : list missing constraints
        // TODO : re-work violation message on product value (cause not clear when validate the whole product)
        $products = [$product];
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        $updater
            ->setValue($products, 'name', 'tooo long name')
            ->setValue($products, 'response_time', 101)
        ;

        // validate the product and values
        $violations = $this->validate($product);
        if (0 === $violations->count()) {
            $output->writeln('<info>Product is valid<info>');
        } else {
            foreach ($violations as $violation) {
                $output->writeln(sprintf("<error>%s<error>", $violation->getMessage()));
            }
        }
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product    = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     *
     * @param ProductInterface
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }
}
