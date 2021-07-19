<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Pim\Enrichment\Component\Product\ReferenceData\LabelRenderer;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Ajax choice reference data factory
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxReferenceDataTransformerFactory implements TransformerFactoryInterface
{
    protected Registry $doctrine;
    protected LabelRenderer $renderer;
    protected string $class;

    public function __construct(Registry $doctrine, LabelRenderer $renderer, string $class)
    {
        $this->doctrine = $doctrine;
        $this->renderer = $renderer;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        $repository = $this->doctrine->getRepository($options['class']);

        return new $this->class($repository, $this->renderer, $options);
    }
}
