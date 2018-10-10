<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Pim\Enrichment\Component\Product\ReferenceData\LabelRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Ajax choice transformer for reference data
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxReferenceDataTransformer implements DataTransformerInterface
{
    /** @var ReferenceDataRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $options;

    /** @var LabelRenderer */
    protected $renderer;

    /**
     * Constructor
     *
     * @param ReferenceDataRepositoryInterface $repository
     * @param LabelRenderer                    $renderer
     * @param array                            $options
     */
    public function __construct(
        ReferenceDataRepositoryInterface $repository,
        LabelRenderer $renderer,
        array $options
    ) {
        $this->repository = $repository;
        $this->renderer = $renderer;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($this->options['multiple']) {
            if (!$value) {
                return [];
            }

            $values = [];
            foreach (explode(',', $value) as $id) {
                $values[] = $this->repository->find($id);
            }

            return $values;
        }

        return $value ? $this->repository->find($value) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($this->options['multiple']) {
            $values = [];
            foreach ($value as $row) {
                $values[] = $row->getId();
            }

            return implode(',', $values);
        }

        return $value ? $value->getId() : '';
    }

    /**
     * Returns the labels corresponding to the given value
     *
     * @param mixed $value
     *
     * @return array
     */
    public function getOptions($value)
    {
        if ($this->options['multiple']) {
            $options = [];

            foreach ($value as $row) {
                $options[] = ['id' => $row->getId(), 'text' => $this->renderer->render($row)];
            }

            return $options;
        }

        return $value
            ? ['id' => $value->getId(), 'text' => $this->renderer->render($value)]
            : null;
    }
}
