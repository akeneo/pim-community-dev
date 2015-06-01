<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a new product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:create')
            ->setDescription('Create a new product')
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
        if (false !== $product) {
            $output->writeln(sprintf('<error>product with identifier "%s" already exists<error>', $identifier));

            return -1;
        }

        $product = $this->createProduct($identifier);
        $violations = $this->validateProduct($product);
        foreach ($violations as $violation) {
            $output->writeln(sprintf("<error>%s<error>", $violation->getMessage()));
        }
        if (0 !== $violations->count()) {
            $output->writeln(sprintf('<error>product "%s" is not valid<error>', $identifier));

            return -1;
        }

        $this->saveProduct($product);
        $output->writeln(sprintf('<info>product "%s" has been updated<info>', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product    = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function createProduct($identifier)
    {
        $builder = $this->getContainer()->get('pim_catalog.builder.product');
        $product = $builder->createProduct($identifier);

        return $product;
    }

    /**
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateProduct(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }

    /**
     * @param ProductInterface $product
     */
    protected function saveProduct(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
    }
}
