<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is used only for testing purpose for a specific CSV format.
 * Please, don't use it on your production environment.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class LoadFranklinProductsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('pimee:suggest-data:load-franklin-products')
            ->addArgument(
                'family',
                InputArgument::REQUIRED,
                '(fridges, hardware, light_bulbs, shelves or watches)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $filepath = realpath(__DIR__) . '/franklin_' . $input->getArgument('family') . '.csv';

        if (!is_file($filepath)) {
            throw new \LogicException(sprintf('Incorrect family "%s"', $input->getArgument('family')));
        }

        $this->createAttribute('pim_asin', AttributeTypes::TEXT);
        $this->createAttribute('UPC', AttributeTypes::TEXT);
        $this->createAttribute('mpn', AttributeTypes::TEXT);
        $this->createAttribute('pim_brand', AttributeTypes::TEXT);

        $fd = fopen($filepath, 'r+');
        $headers = fgetcsv($fd);
        $headers[1] = 'pim_asin';
        $headers[3] = 'pim_brand';
        $headers[4] = 'mpn';

        $family = $this->createFamily($input->getArgument('family'), $headers);

        while ($dataRow = fgetcsv($fd)) {
            $this->createProduct(array_combine($headers, $dataRow), $family);
        }
    }

    /**
     * @param string $attrCode
     * @param string $attrType
     *
     * @return AttributeInterface
     */
    private function createAttribute(string $attrCode, string $attrType = AttributeTypes::TEXT): AttributeInterface
    {
        $attrCode = str_replace(' ', '', $attrCode);
        $attribute = $this->getContainer()->get('pim_catalog.repository.attribute')->findOneByIdentifier($attrCode);
        if (null === $attribute) {
            $attributeFactory = $this->getContainer()->get('pim_catalog.factory.attribute');
            $attribute = $attributeFactory->createAttribute($attrType);
            $attribute->setCode($attrCode);
            $attribute->setGroup($this->getOtherAttributeGroup());
            $attribute->setUseableAsGridFilter(true);

            $this->getContainer()->get('pim_catalog.saver.attribute')->save($attribute);
        }

        return $attribute;
    }

    /**
     * @param string $familyCode
     * @param array $attrCodes
     *
     * @return FamilyInterface
     */
    private function createFamily(string $familyCode, array $attrCodes): FamilyInterface
    {
        $family = $this->getContainer()->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        if (null === $family) {
            $family = $this->getContainer()->get('pim_catalog.factory.family')->create();
            $family->setCode($familyCode);
        }
        foreach ($attrCodes as $attrCode) {
            $attribute = $this->createAttribute($attrCode);
            $family->addAttribute($attribute);
        }

        $this->getContainer()->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param array $rawValues
     * @param FamilyInterface $family
     *
     * @return ProductInterface
     */
    private function createProduct(array $rawValues, FamilyInterface $family): ProductInterface
    {
        $product = $this->getContainer()
            ->get('pim_catalog.repository.product')
            ->findOneByIdentifier($rawValues['SKU']);

        if (null === $product) {
            $productBuilder = $this->getContainer()->get('pim_catalog.builder.product');
            $product = $productBuilder->createProduct($rawValues['SKU'], $family->getCode());
        }

        $valueFactory = $this->getContainer()->get('pim_catalog.factory.value');
        foreach ($rawValues as $attrCode => $rawValue) {
            if (empty($rawValue)) {
                continue;
            }
            if ('UPC' === $attrCode) {
                $rawValue = str_pad($rawValue, 12, '0', STR_PAD_LEFT);
            }
            $product->addValue(
                $valueFactory->create(
                    $this->createAttribute($attrCode),
                    null,
                    null,
                    $rawValue
                )
            );
        }

        $this->getContainer()->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @return AttributeGroupInterface|null
     */
    private function getOtherAttributeGroup(): ?AttributeGroupInterface
    {
        $attrGroupRepository = $this->getContainer()->get('pim_catalog.repository.attribute_group');

        return $attrGroupRepository->findOneByIdentifier('other');
    }
}
