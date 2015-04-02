<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Updates and validates a product
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements UpdaterInterface
{
    /** @var ProductRawUpdaterInterface */
    protected $rawUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductRawUpdaterInterface $rawUpdater
     * @param ValidatorInterface         $validator
     */
    public function __construct(
        ProductRawUpdaterInterface $rawUpdater,
        ValidatorInterface $validator
    ) {
        $this->rawUpdater = $rawUpdater;
        $this->validator  = $validator;
    }

    /**
     * {@inheritdoc}
     * $data could be formatted like this:
     * [
     *      ['set_data' =>
     *          [
     *              ['categories', ['shirts', 'jeans'], ['locale' => 'fr_FR']],
     *              ['price', [{ "data": 12.3, "currency": "EUR" }, { "data": 12, "currency": "USD" }]]],
     *              ['description', 'A beautiful tee', ['locale' => 'en_US', 'scope' => 'mobile']]
     *              ...
     *          ]
     *      ]
     *      ['add_data' => [ ['tshirt_style', ['vneck'], []], [second data to add], ...] ]
     *      ['copy_data' => [ [first data to copy], [second data to copy], ...] ]
     *      ['remove_data' => [ [first data to remove], [second data to remove], ...] ]
     * ]
     * OR like this
     * [
     *      ['set_data', 'categories', ['shirts', 'jeans'], ['locale' => 'fr_FR']],
     *      ['set_data', 'price', [{ "data": 12.3, "currency": "EUR" }, { "data": 12, "currency": "USD" }]]],
     *      ['set_data', 'description', 'A beautiful tee', ['locale' => 'en_US', 'scope' => 'mobile']]
     *      ['add_data', 'tshirt_style', ['vneck']],
     *      ...
     * ]
     *
     * First implementation coded here.
     *
     * Problems:
     *    what about copy products ? (from / to)
     */
    public function update($product, array $data)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                "Product updater expects a Pim\\Bundle\\CatalogBundle\\Model\\ProductInterface."
            );
        }

        try {
            $this->checkData($data);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid data for product updater", 0, $e);
        }

        $updaterViolations = new ConstraintViolationList();
        try {
            $this->applyRawUpdates($product, $data);
        } catch (\InvalidArgumentException $e) {
            //TODO: fix this dirty hack
            $updaterViolations->add(new ConstraintViolation($e->getMessage(), $e->getMessage(), [], '', '', ''));
        }

        $violations = $this->validator->validate($product);
        $violations->addAll($updaterViolations);

        if ($violations->count() > 0) {
            throw new BusinessValidationException(
                $violations,
                sprintf('Business violations for product "%s".', $product->getIdentifier())
            );
        }

        return $product;
    }

    /**
     * Apply raw updates on a product
     *
     * @param ProductInterface $product
     * @param array            $rawUpdates
     */
    protected function applyRawUpdates(ProductInterface $product, array $rawUpdates)
    {
        foreach ($rawUpdates as $updateType => $updates) {
            foreach ($updates as $update) {
                $this->checkSingleUpdateData($update);

                $field   = $update[0];
                $value   = $update[1];
                $options = isset($update[2]) ? $update[2] : [];

                $method = lcfirst(str_replace('_', '', ucwords($update)));
                $this->$method($product, $field, $value, $options);

                //TODO: what about copy products ??
            }
        }
    }

    /**
     * Checks the format the updates data
     *
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    private function checkData(array $data)
    {
        $options = new OptionsResolver();
        $options->setOptional(['set_data', 'add_data', 'copy_data', 'remove_data']);
        $options->addAllowedTypes(
            [
                'set_data'    => 'array',
                'add_data'    => 'array',
                'copy_data'   => 'array',
                'remove_data' => 'array'
            ]
        );

        $options->resolve($data);
    }

    /**
     * Check the format of a single update data
     *
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    private function checkSingleUpdateData(array $data)
    {
        if (count($data) > 3 || count($data) < 2) {
            //TODO
            throw new \InvalidArgumentException();
        }

        if (3 === count($data) && !is_array($data[2])) {
            //TODO
            throw new \InvalidArgumentException();
        }
    }
}
