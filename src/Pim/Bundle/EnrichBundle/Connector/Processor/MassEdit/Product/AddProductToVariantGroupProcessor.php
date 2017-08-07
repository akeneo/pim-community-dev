<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product to a variant group in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductToVariantGroupProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /**
     * @param ValidatorInterface                  $validator
     * @param GroupRepositoryInterface            $groupRepository
     * @param ProductTemplateUpdaterInterface     $templateUpdater
     */
    public function __construct(
        ValidatorInterface $validator,
        GroupRepositoryInterface $groupRepository,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->validator = $validator;
        $this->groupRepository = $groupRepository;
        $this->templateUpdater = $templateUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $variantGroup = $actions[0]['value'];
        $variantGroup = $this->groupRepository->findOneByIdentifier($variantGroup);

        $variantGroup->addProduct($product);

        if (null !== $variantGroup->getProductTemplate()) {
            $this->templateUpdater->update($variantGroup->getProductTemplate(), [$product]);
        }

        if (null === $product || (null !== $product && !$this->isProductValid($product))) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
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
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }
}
