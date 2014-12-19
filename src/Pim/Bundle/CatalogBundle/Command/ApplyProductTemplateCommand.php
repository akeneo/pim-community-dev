<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Temporary command to import product templates
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApplyProductTemplateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:variant-group:apply-template');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Fetch the values from the stored template
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        $template = $variantGroup->getProductTemplate();
        $products = $variantGroup->getProducts();
        $products = $products->count() > 0 ? $products->toArray() : [];

        $this->updateAll($products, $template);
        $this->validateAll($products, $output);
        $this->saveAll($products);

        $output->writeln(
            sprintf(
                '<info>%d products in variant group "%s" have been updated<error>',
                count($products),
                $variantGroup->getCode()
            )
        );
    }

    /**
     * @param string $code
     *
     * @return Group
     */
    protected function getVariantGroup($code)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.group');
        $group      = $repository->findOneByCode($code);

        return $group;
    }

    /**
     * @param ProductInterface[]       $products
     * @param ProductTemplateInterface $template
     */
    protected function updateAll($products, ProductTemplateInterface $template)
    {
        /** @var ProductTemplateUpdaterInterface */
        $updater = $this->getContainer()->get('pim_catalog.updater.product_template');
        $updater->update($products, $template);
    }

    /**
     * @param ProductInterface[] $products
     * @param OutputInterface    $output
     */
    protected function validateAll($products, OutputInterface $output)
    {
        foreach ($products as $product) {
            $violations = $this->validateProduct($product);
            foreach ($violations as $violation) {
                $output->writeln(sprintf("<error>%s : %s<error>", $violation->getMessage(), $violation->getInvalidValue()));
            }
            if (0 !== $violations->count()) {
                $output->writeln(sprintf('<error>product "%s" is not valid<error>', $product->getIdentifier()));
                $detacher = $this->getContainer()->get('pim_catalog.doctrine.detacher');
                $detacher->detach($product);
            }
        }
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
     * @param ProductInterface[] $products
     */
    protected function saveAll($products)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->saveAll($products);
    }
}
