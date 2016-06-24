<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\MassEditAction\Operation;

use Akeneo\Component\Classification\Model\TagInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation;

/**
 * Batch operation that adds tags on assets.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class AddTags extends AbstractMassEditOperation
{
    /** @var string */
    protected $formType;

    /** @var TagInterface[] */
    protected $tags = [];

    /**
     * @param string $jobInstanceCode
     * @param string $formType
     */
    public function __construct($jobInstanceCode, $formType)
    {
        $this->jobInstanceCode = $jobInstanceCode;
        $this->formType        = $formType;
    }

    /**
     * @return TagInterface[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param TagInterface[] $tags
     *
     * @return AddTags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'add-tags-to-assets';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $tags = $this->getTags();

        return [
            [
                'field' => 'tags',
                'value' => $this->getTagCodes($tags),
            ]
        ];
    }

    /**
     * @param TagInterface[] $tags
     *
     * @return string[]
     */
    protected function getTagCodes(array $tags)
    {
        return array_map(
            function (TagInterface $tag) {
                return $tag->getCode();
            },
            $tags
        );
    }
}
