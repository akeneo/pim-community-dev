<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix PIM-7263.
 *
 * If some variant products have a some boolean values at their variation level that should belong to their
 * parents instead, this command will remove these values.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveWrongBooleanValuesOnVariantProductsCommand extends ContainerAwareCommand
{
    private const PRODUCT_BULK_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHidden(true)
            ->setName('pim:catalog:remove-wrong-values-on-variant-products')
            ->setDescription('Remove boolean values on variant products that should belong to their parents')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("<info>Cleaning wrong boolean values on variant products...</info>");

        $variantProducts = $this->getVariantProducts();

        $totalProducts = 0;
        $cleanProducts = 0;
        $productsToSave = [];

        foreach ($variantProducts as $variantProduct) {
            $totalProducts++;
            $isModified = false;

            // TODO: Drop this check for 2.2 version
            if (!($variantProduct instanceof VariantProductInterface)) {
                continue;
            }

            foreach ($variantProduct->getFamily()->getAttributes() as $attribute) {
                if ($this->isProductImpacted($variantProduct, $attribute)) {
                    $this->cleanProductForAttribute($variantProduct, $attribute);
                    $isModified = true;
                }
            }

            if ($isModified) {
                $violations = $this->getContainer()->get('pim_catalog.validator.product')->validate($variantProduct);

                if ($violations->count() > 0) {
                    throw new \LogicException(
                        sprintf(
                            'Product "%s" is not valid and cannot be saved',
                            $variantProduct->getIdentifier()
                        )
                    );
                }

                $productsToSave[] = $variantProduct;
                $cleanProducts++;
            }

            if (count($productsToSave) >= self::PRODUCT_BULK_SIZE) {
                $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
                $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product')->indexAll($productsToSave);

                $productsToSave = [];
            }
        }

        if (!empty($productsToSave)) {
            $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
            $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product')->indexAll($productsToSave);
        }

        $output->writeln(sprintf('<info>%s variant products cleaned (over %s products parsed)</info>', $cleanProducts, $totalProducts));
    }

    /**
     * @return CursorInterface
     */
    private function getVariantProducts(): CursorInterface
    {
        $pqb = $this->getContainer()
            ->get('pim_catalog.query.product_and_product_model_query_builder_factory')
            ->create();

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null);

        return $pqb->execute();
    }

    /**
     * @param mixed              $variantProduct
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    private function isProductImpacted($variantProduct, AttributeInterface $attribute): bool
    {
        if ($attribute->getType() !== AttributeTypes::BOOLEAN) {
            return false;
        }

        $familyVariant = $variantProduct->getFamilyVariant();
        $attributeLevel = $familyVariant->getLevelForAttributeCode($attribute->getCode());
        $attributeIsOnLastLevel = $attributeLevel === $familyVariant->getNumberOfLevel();

        if ($attributeIsOnLastLevel) {
            return false;
        }

        return null !== $variantProduct->getValuesForVariation()->getByCodes($attribute->getCode());
    }

    /**
     * @param mixed              $variantProduct
     * @param AttributeInterface $attribute
     */
    private function cleanProductForAttribute($variantProduct, AttributeInterface $attribute): void
    {
        $values = $variantProduct->getValues();
        $values->removeByAttribute($attribute);
        $variantProduct->setValues($values);
    }
}
