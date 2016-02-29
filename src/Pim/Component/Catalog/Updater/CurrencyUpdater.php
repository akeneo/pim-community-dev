<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\CurrencyInterface;

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
     *     'code'      => 'USD',
     *     'activated' => true,
     *     ]
     * ]
     */
    public function update($attributeGroup, array $data, array $options = [])
    {
        if (!$attributeGroup instanceof CurrencyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\CurrencyInterface", "%s" provided.',
                    ClassUtils::getClass($attributeGroup)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($attributeGroup, $field, $value);
        }

        return $this;
    }

    /**
     * @param CurrencyInterface $currency
     * @param string            $field
     * @param mixed             $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData($currency, $field, $data)
    {
        if ('code' == $field) {
            $currency->setCode($data);
        } elseif ('activated' == $field) {
            $currency->setActivated($data);
        }
    }
}
