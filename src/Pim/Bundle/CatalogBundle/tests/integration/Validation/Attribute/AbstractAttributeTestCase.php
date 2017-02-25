<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Validation\Attribute;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeTestCase extends TestCase
{
    /**
     * @param $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByCode($code);
    }

    /**
     * @return AttributeInterface
     */
    protected function createAttribute()
    {
        return $this->get('pim_catalog.factory.attribute')->create();
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     */
    protected function updateAttribute(AttributeInterface $attribute, array $data)
    {
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateAttribute(AttributeInterface $attribute)
    {
        return $this->get('validator')->validate($attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
