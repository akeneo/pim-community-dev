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

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Classification\Repository\TagRepositoryInterface;
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\TagInterface", "%s" provided.',
                    ClassUtils::getClass($tag)
                )
            );
        }

        foreach ($data as $field => $item) {
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
        switch ($field) {
            case 'code':
                $this->setCode($tag, $data);
                break;
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
}
