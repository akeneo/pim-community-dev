<?php

namespace Akeneo\Channel\Component\Updater;

use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a currency
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'    => 'USD',
     *     'enabled' => true,
     * ]
     */
    public function update($currency, array $data, array $options = [])
    {
        if (!$currency instanceof CurrencyInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($currency),
                CurrencyInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($currency, $field, $value);
        }

        return $this;
    }

    /**
     * @param CurrencyInterface $currency
     * @param string            $field
     * @param mixed             $data
     */
    protected function setData(CurrencyInterface $currency, $field, $data)
    {
        if ('code' == $field) {
            $currency->setCode($data);
        } elseif ('enabled' == $field) {
            $currency->setActivated($data);
        }
    }
}
