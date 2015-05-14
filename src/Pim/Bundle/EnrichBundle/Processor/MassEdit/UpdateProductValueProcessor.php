<?php

namespace Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Pim\Bundle\BaseConnectorBundle\Model\Repository\JobConfigurationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductFieldUpdaterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processor to update product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductValueProcessor extends AbstractMassEditProcessor
{
    /** @var ProductFieldUpdaterInterface */
    protected $productFieldUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductFieldUpdaterInterface        $productFieldUpdater
     * @param ValidatorInterface                  $validator
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     */
    public function __construct(
        ProductFieldUpdaterInterface $productFieldUpdater,
        ValidatorInterface $validator,
        JobConfigurationRepositoryInterface $jobConfigurationRepo
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->productFieldUpdater = $productFieldUpdater;
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

        $this->setData($product, $actions);

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
     * Set data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return UpdateProductValueProcessor
     */
    protected function setData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->productFieldUpdater->setData($product, $action['field'], $action['value']);
        }

        return $this;
    }
}
