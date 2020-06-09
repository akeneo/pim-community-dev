<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Asset\Bundle\Datagrid\Filter;

use Akeneo\Asset\Component\Model\Tag;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Tag filter
 *
 * @author JM Leroux <jean-marie@akeneo.com>
 */
class TagFilter extends AjaxChoiceFilter
{
    /** @var TagFilterAwareInterface */
    protected $util;

    /** @var TagRepositoryInterface */
    private $tagRepository;

    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        TagRepositoryInterface $tagRepository = null
    ) {
        parent::__construct($factory, $util);
        $this->tagRepository = $tagRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $dataSource, $data)
    {
        $filterColumn = $this->get(FilterUtility::DATA_NAME_KEY);
        $operator = $this->getOperator($data['type']);

        if (!empty($data['value'])) {
            $data['value'] = $this->replaceCodesByIds($data['value']);
        }

        $this->util->applyTagFilter($dataSource, $filterColumn, $operator, $data['value']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['emptyChoice'] = true;

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url' => 'pim_ui_ajaxentity_list',
                'choice_url_params' => [
                    'class' => Tag::class,
                    'options' => [
                        'expanded' => 0,
                    ],
                ],
            ]
        );
    }

    private function replaceCodesByIds(array $codes)
    {
        if (null === $this->tagRepository) {
            return $codes;
        }

        foreach ($codes as $key => $code) {
            /** @var Tag|null $tag */
            $tag = $this->tagRepository->findOneByIdentifier($code);
            if (null !== $tag) {
                $codes[$key] = $tag->getId();
            }
        }

        return $codes;
    }
}
