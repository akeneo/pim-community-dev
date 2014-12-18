<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupProductTemplate;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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
class ImportProductTemplateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:import:template');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup the template with an existing product
        $referenceProduct = $this->getProduct('AKNTS_BPS');
        $productValues = $referenceProduct->getValues()->toArray();
        $productValuesData = $this->normalizeToDB($productValues);

        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new GroupProductTemplate();
        }
        $template->setValuesData($productValuesData);
        $template->setGroup($variantGroup);
        $this->saveTemplate($template); // TODO : should use cascade on VG

        // Fetch the values from the stored template
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        $readProductTemplate = $variantGroup->getProductTemplate();
        $rawValuesData = $readProductTemplate->getValuesData();
        $values = $this->denormalizeFromDB($rawValuesData);

        // Apply the update on another products
        $updates = $this->normalizeToUpdate($values);

        // TODO unset identifier and axis updates and picture (not supported for now)
        $skippedAttributes = ['sku', 'main_color', 'secondary_color', 'clothing_size', 'picture'];
        foreach ($updates as $indexUpdate => $update) {
            if (in_array($update['attribute'], $skippedAttributes)) {
                unset($updates[$indexUpdate]);
            } elseif (null === $update['value']) {
                // TODO ugly fix on null string
                $updates[$indexUpdate]['value'] = "";
            }
        }

        // TODO picture doesnt work
        // TODO prices doesnt work

        $products = $variantGroup->getProducts();
        $products = $products->count() > 0 ? $products->toArray() : [];

        $this->updateAll($products, $updates);
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
     * @param array $rawProductValues
     *
     * @return ProductValueInterface[]
     */
    protected function denormalizeFromDB(array $rawProductValues)
    {
        $denormalizer = $this->getContainer()->get('pim_serializer');
        $fieldNameBuilder = $this->getContainer()->get('pim_transform.builder.field_name');
        $productValueClass = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
        $productValues = [];

        foreach ($rawProductValues as $attFieldName => $dataValue) {
            $attributeInfos = $fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];
            $value = new ProductValue();
            $value->setAttribute($attribute);
            $value->setLocale($attributeInfos['locale_code']);
            $value->setScope($attributeInfos['scope_code']);
            unset($attributeInfos['attribute']);
            unset($attributeInfos['locale_code']);
            unset($attributeInfos['scope_code']);

            // TODO : as for versionning, we should really change for json/structured format
            $productValues[] = $denormalizer->denormalize(
                $dataValue,
                $productValueClass,
                'csv',
                ['entity' => $value] + $attributeInfos
            );
        }

        $valuesCollection = new ArrayCollection();
        foreach ($productValues as $value) {
            $valuesCollection[ProductValueKeyGenerator::getKey($value)] = $value;
        }

        return $valuesCollection;
    }

    /**
     * @param ArrayCollection $productValues
     *
     * @return array
     */
    protected function normalizeToUpdate(ArrayCollection $productValues)
    {
        $normalizer = $this->getContainer()->get('pim_serializer');
        $normalizedValues = [];
        foreach ($productValues as $value) {
            $update = [
                // TODO : weird result with price
                'value' => $normalizer->normalize($value->getData(), 'json', ['locales' => []]),
                'attribute' => $value->getAttribute()->getCode(),
                'locale' => $value->getLocale(),
                'scope' => $value->getScope()
            ];
            $normalizedValues[] = $update;
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
     * @param GroupProductTemplate $template
     */
    protected function saveTemplate(GroupProductTemplate $template)
    {
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $objectManager->persist($template);
        $objectManager->flush();
    }

    /**
     * @param ProductInterface[] $products
     * @param array              $updates
     */
    protected function updateAll($products, $updates)
    {
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        foreach ($updates as $update) {
            $updater->setValue(
                $products,
                $update['attribute'],
                $update['value'],
                $update['locale'],
                $update['scope']
            );
        }
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
                $output->writeln(sprintf("<error>%s<error>", $violation->getMessage()));
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
