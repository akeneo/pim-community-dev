<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueProcessor extends AbstractProcessor
{
    /** @var PropertyAdderInterface */
    protected $propertyAdder;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param PropertyAdderInterface              $propertyAdder
     * @param ValidatorInterface                  $validator
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     */
    public function __construct(
        PropertyAdderInterface $propertyAdder,
        ValidatorInterface $validator,
        JobConfigurationRepositoryInterface $jobConfigurationRepo
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->propertyAdder = $propertyAdder;
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
            $this->propertyAdder->addData($product, $action['field'], $action['value']);
        }

        return $this;
    }
}
