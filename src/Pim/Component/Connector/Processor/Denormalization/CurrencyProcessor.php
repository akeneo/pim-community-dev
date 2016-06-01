<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\CurrencyFactory;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Currency import processor
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $currencyConverter;

    /** @var CurrencyFactory */
    protected $currencyFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $currencyConverter
     * @param CurrencyFactory                       $currencyFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $currencyConverter,
        CurrencyFactory $currencyFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->currencyConverter = $currencyConverter;
        $this->currencyFactory   = $currencyFactory;
        $this->updater           = $updater;
        $this->validator         = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->currencyConverter->convert($item);
        $currency = $this->findOrCreateCurrency($convertedItem);

        try {
            $this->updater->update($currency, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($currency);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $currency;
    }

    /**
     * @param array $convertedItem
     *
     * @return CurrencyInterface
     */
    protected function findOrCreateCurrency(array $convertedItem)
    {
        $currency = $this->findObject($this->repository, $convertedItem);
        if (null === $currency) {
            return $this->currencyFactory->create();
        }

        return $currency;
    }
}
