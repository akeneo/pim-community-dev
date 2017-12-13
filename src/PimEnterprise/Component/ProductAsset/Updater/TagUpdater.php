<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;

/**
 * Updates and validates a tag
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class TagUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     "code": "mycode",
     * }
     */
    public function update($tag, array $data, array $options = [])
    {
        if (!$tag instanceof TagInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($tag),
                TagInterface::class
            );
        }

        foreach ($data as $field => $item) {
            $this->validateDataType($field, $item);
            $this->setData($tag, $field, $item);
        }

        return $this;
    }

    /**
     * @param TagInterface $tag
     * @param string       $field
     * @param mixed        $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(TagInterface $tag, $field, $data)
    {
        if ('code' === $field) {
            $this->setCode($tag, $data);
        }
    }

    /**
     * @param TagInterface $tag
     * @param string       $code
     */
    protected function setCode(TagInterface $tag, $code)
    {
        $tag->setCode($code);
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, $data)
    {
        if ('code' === $field) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }
}
