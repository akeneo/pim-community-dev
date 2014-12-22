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
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        $template = $variantGroup->getProductTemplate();
        $products = $variantGroup->getProducts();
        $products = $products->count() > 0 ? $products->toArray() : [];

        $skipped = $this->apply($template, $products);
        $nbSkipped = count($skipped);
        foreach ($skipped as $productIdentifier => $messages) {
            $output->writeln(sprintf('<error>product "%s" is not valid<error>', $productIdentifier));
            foreach ($messages as $message) {
                $output->writeln(sprintf("<error>%s<error>", $message));
            }
        }

        $output->writeln(
            sprintf(
                '<info>%d products in variant group "%s" have been updated<error>',
                count($products) - $nbSkipped,
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
     * @param ProductTemplateInterface $template
     * @param ProductInterface[]       $products
     *
     * @return array $violations
     */
    protected function apply(ProductTemplateInterface $template, $products)
    {
        $updater = $this->getContainer()->get('pim_catalog.manager.product_template');
        return $updater->apply($template, $products);
    }
}
