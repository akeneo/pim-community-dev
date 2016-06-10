<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\CurrencyFactory;
use Pim\Component\Catalog\Model\CurrencyInterface;
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
    /** @var CurrencyFactory */
    protected $currencyFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param CurrencyFactory                       $currencyFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        CurrencyFactory $currencyFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->currencyFactory   = $currencyFactory;
        $this->updater           = $updater;
        $this->validator         = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $currency = $this->findOrCreateCurrency($item);

        try {
            $this->updater->update($currency, $item);
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
     * @param array $item
     *
     * @return CurrencyInterface
     */
    protected function findOrCreateCurrency(array $item)
    {
        $currency = $this->findObject($this->repository, $item);
        if (null === $currency) {
            return $this->currencyFactory->create();
        }

        return $currency;
    }
}
