<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\ProductTemplate;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO : to delete
 *
 * Temporary command to create variant group product templates
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductTemplateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:variant-group:create-template');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');

        // Setup the template with an existing product
        $referenceProduct = $this->getProduct('AKNTS_BPS');
        $productValues = $referenceProduct->getValues()->toArray();
        // TODO HotFix to skip images until we add support
        $skipAttributeTypes = ['pim_catalog_identifier'];
        $skipAxisAttributes = [];
        foreach ($variantGroup->getAttributes() as $axis) {
            $skipAxisAttributes[]= $axis->getCode();
        }

        foreach ($productValues as $valueIdx => $value) {
            if (in_array($value->getAttribute()->getAttributeType(), $skipAttributeTypes)) {
                unset($productValues[$valueIdx]);
            }
            if (in_array($value->getAttribute()->getCode(), $skipAxisAttributes)) {
                unset($productValues[$valueIdx]);
            }
        }
        $productValuesData = $this->normalizeToDB($productValues);

        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new ProductTemplate();
            $variantGroup->setProductTemplate($template);
        }
        $template->setValuesData($productValuesData);
        $this->saveVariantGroup($variantGroup);

        $output->writeln(
            sprintf(
                '<info>product template has been created for variant group "%s"<error>',
                $variantGroup->getCode()
            )
        );
    }

    /**
     * @param ProductValueInterface[] $productValues
     *
     * @return array
     */
    protected function normalizeToDB(array $productValues)
    {
        // TODO : as for versionning, we should really change for json/structured format
        $normalizer = $this->getContainer()->get('pim_serializer');
        $normalizedValues = [];
        foreach ($productValues as $value) {
            $normalizedValues += $normalizer->normalize($value, 'csv');
        }

        return $normalizedValues;
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
     * @param Group $group
     */
    protected function saveVariantGroup(Group $group)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.group');
        $saver->save($group);
    }
}
