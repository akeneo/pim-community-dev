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
        $this->save($template); // TODO : should use cascade on VG

        // Fetch the values from the stored template
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        $readProductTemplate = $variantGroup->getProductTemplate();
        $rawValuesData = $readProductTemplate->getValuesData();
        $values = $this->denormalizeFromDB($rawValuesData);

        // Apply the update on another products
        $updates = $this->normalizeToUpdate($values);
        var_dump($updates);

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
     * @param ArrayCollection $productValues
     *
     * @return array
     */
    protected function normalizeToUpdate(ArrayCollection $productValues)
    {
        $normalizer = $this->getContainer()->get('pim_serializer');
        $normalizedValues = [];
        foreach ($productValues as $value) {
            $normalizedValues[] = $normalizer->normalize($value, 'json', ['locales' => []]);
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
    protected function save(GroupProductTemplate $template)
    {
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $objectManager->persist($template);
        $objectManager->flush();
    }
}
