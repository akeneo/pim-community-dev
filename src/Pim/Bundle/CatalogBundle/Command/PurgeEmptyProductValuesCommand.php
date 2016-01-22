<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Purge empty product values, depending on the attribute type, the value is considered empty on different criteria
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEmptyProductValuesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:purge-empty-product-values')
            ->setDescription('Purge all empty product values, please dump your database first!');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $products = $this->getAllProducts();
        foreach ($products as $product) {
            $purgedProduct = $this->removeProductValues($product);
            if (true === $purgedProduct) {
                $violations = $this->validate($product);
                foreach ($violations as $violation) {
                    $output->writeln(sprintf("<error>%s</error>", $violation->getMessage()));
                }
                $identifier = $product->getIdentifier()->getData();
                if (0 === $violations->count()) {
                    $this->save($product);
                    $output->writeln(sprintf('<info>Product "%s" has been cleaned up</info>', $identifier));
                } else {
                    $output->writeln(sprintf('<error>Once purged the product "%s" is not valid</error>', $identifier));
                }
            }
            $this->detach($product);

            $mem  = memory_get_usage(true); // 123 kb
            $unit =array('b','kb','mb','gb','tb','pb');
            $mem  = @round($mem/pow(1024,($i=floor(log($mem,1024)))),2).' '.$unit[$i];
            $output->writeln($mem);
        }
        $output->writeln(sprintf('<info>Empty product values has been purged from your database</info>'));

        return 0;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool has removed values
     */
    protected function removeProductValues(ProductInterface $product)
    {
        $purgedProduct = false;
        foreach ($product->getValues() as $value) {

            if ($value->getData() === null) {
                $product->removeValue($value);
                $purgedProduct = true;

            } elseif (AttributeTypes::PRICE_COLLECTION === $value->getAttribute()->getAttributeType()) {
                $fulfilledPrice = false;
                foreach ($value->getData() as $price) {
                    if (null !== $price->getData()) {
                        $fulfilledPrice = true;
                    }
                }
                if (false === $fulfilledPrice) {
                    $product->removeValue($value);
                    $purgedProduct = true;
                }

            } elseif (AttributeTypes::METRIC === $value->getAttribute()->getAttributeType()) {
                if (null === $value->getData()->getData()) {
                    $product->removeValue($value);
                    $purgedProduct = true;
                }
            }
        }

        return $purgedProduct;
    }

    /**
     * @return CursorInterface
     */
    protected function getAllProducts()
    {
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');
        $productQueryBuilder = $factory->create();

        return $productQueryBuilder->execute();
    }

    /**
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }

    /**
     * @param ProductInterface $product
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
    }

    /**
     * @param ProductInterface $product
     */
    protected function detach(ProductInterface $product)
    {
        $detacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');
        $detacher->detach($product);
    }
}
