<?php

namespace Pim\Component\Catalog\Model\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Product value for pim_catalog_option
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionProductValue extends AbstractProductValue
{
    /** @var array */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->option;
    }

    /**
     * @return array
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->option;
    }

    /**
     * {@inheritdoc}
     */
    protected function setData($data = null)
    {
        if (null === $data) {
            return null;
        }

        if (!$data instanceof AttributeOptionInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($data), AttributeOptionInterface::class);
        }

        $this->option = $data;
    }
}
