<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Updater\Remover;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;


/**
 * TODO
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongBooleanValuesOnVariantProductsRemover
{
    /** @var VariantProductInterface[]  */
    protected $productsToSave = [];

    /** @var RecursiveValidator  */
    protected $validator;

    /** @var BulkSaverInterface  */
    protected $saver;

    /** @var BulkIndexerInterface  */
    protected $indexer;

    public function __construct(
        RecursiveValidator $validator,
        BulkSaverInterface $saver,
        BulkIndexerInterface $indexer
    ) {
        $this->validator = $validator;
        $this->saver = $saver;
        $this->indexer = $indexer;
    }

    /**
     * @param VariantProductInterface $variantProduct
     * @param int                     $productBatchSize
     *
     * @return array
     */
    public function removeWrongBooleanValues(VariantProductInterface $variantProduct, int $productBatchSize): array
    {
        $isModified = false;

        foreach ($variantProduct->getFamily()->getAttributes() as $attribute) {
            if ($this->isProductImpacted($variantProduct, $attribute)) {
                $this->cleanProductForAttribute($variantProduct, $attribute);
                $isModified = true;
            }
        }

        if ($isModified) {
            $violations = $this->validator->validate($variantProduct);

            if ($violations->count() > 0) {
                throw new \LogicException(
                    sprintf(
                        'Product "%s" is not valid and cannot be saved',
                        $variantProduct->getIdentifier()
                    )
                );
            }

            $this->productsToSave[] = $variantProduct;
        }

        if (count($this->productsToSave) >= $productBatchSize) {
            $this->saver->saveAll($this->productsToSave);
            $this->indexer->indexAll($this->productsToSave);

            $this->productsToSave = [];
        }

        return $this->productsToSave;
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

        /** @var FamilyVariantInterface $familyVariant */
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
        /** @var ValueCollectionInterface $values */
        $values = $variantProduct->getValues();
        $values->removeByAttribute($attribute);
        $variantProduct->setValues($values);
    }
}
