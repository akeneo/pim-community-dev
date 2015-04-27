<?php

namespace Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueProcessor extends AbstractMassEditProcessor
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductUpdaterInterface     $productUpdater
     * @param ValidatorInterface          $validator
     * @param MassEditRepositoryInterface $massEditRepository
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        MassEditRepositoryInterface $massEditRepository
    ) {
        parent::__construct($massEditRepository);
        $this->productUpdater = $productUpdater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $configuration = $this->getJobConfiguration();

        if (!array_key_exists('actions', $configuration)) {
            throw new InvalidArgumentException('Missing configuration for \'actions\'.');
        }

        $actions = $configuration['actions'];

        $this->addData($product, $actions);

        if (null === $product || (null !== $product && !$this->isProductValid($product))) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        $this->stepExecution->incrementSummaryInfo('mass_edited');

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

    /**
     * Add data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return AddProductValueProcessor
     */
    protected function addData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->productUpdater->addData($product, $action['field'], $action['value']);
        }

        return $this;
    }
}
