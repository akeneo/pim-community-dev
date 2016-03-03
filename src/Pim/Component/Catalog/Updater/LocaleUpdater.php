<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * Updates a locale
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'code': 'en_US'
     * }
     */
    public function update($locale, array $data, array $options = [])
    {
        if (!$locale instanceof LocaleInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\LocaleInterface", "%s" provided.',
                    ClassUtils::getClass($locale)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($locale, $field, $value);
        }

        return $this;
    }

    /**
     * @param LocaleInterface $locale
     * @param string          $field
     * @param mixed           $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(LocaleInterface $locale, $field, $data)
    {
        if ('code' === $field) {
            $locale->setCode($data);
        }
    }
}
