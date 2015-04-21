<?php

namespace Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesProcessor extends AbstractMassEditProcessor implements ItemProcessorInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeRepositoryInterface */
    protected $massActionRepository;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var array */
    protected $skippedAttributes = [];

    /**
     * @param ProductUpdaterInterface              $productUpdater
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->productUpdater       = $productUpdater;
        $this->validator            = $validator;
        $this->attributeRepository  = $attributeRepository;
        $this->massActionRepository = $massActionRepository;
    }

    /**
     * Set data from $actions to the given $products
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return array $products
     */
    protected function updateProduct(ProductInterface $product, array $actions)
    {
        $nbModifiedAttributes = 0;
        foreach ($actions as $action) {
            $attribute = $this->attributeRepository->findOneByCode($action['field']);

            if (null === $attribute) {
                throw new \LogicException(sprintf('Attribute with code %s does not exist'), $action['field']);
            }
            $family = $product->getFamily();

            if (null !== $family && $family->hasAttribute($attribute)) {
                $this->productUpdater->setData($product, $action['field'], $action['value'], $action['options']);
                $nbModifiedAttributes++;
            }
        }
        if (0 > $nbModifiedAttributes) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                $this->getName(),
                'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
                [],
                $product
            );
        }

        return $product;
    }

    /**
     * Validate the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isProductValid(ProductInterface $product)
    {
        $isValid = false;
        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $isValid = true;
        } else {
            $this->addWarningMessage($violations, $product);
            $this->stepExecution->incrementSummaryInfo('skipped_products');
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $actions = $item['actions'];
        $product = $item['product'];

        $product = $this->updateProduct($product, $actions);
        if (null !== $product && !$this->isProductValid($product)) {
            return null;
        }

        return $product;
    }
}
