<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Pim\Enrichment\Component\Product\ReferenceData\LabelRenderer;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Ajax choice reference data factory
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxReferenceDataTransformerFactory implements TransformerFactoryInterface
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var LabelRenderer */
    protected $renderer;

    /** @var string */
    protected $class;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param LabelRenderer     $renderer
     * @param string            $class
     */
    public function __construct(RegistryInterface $doctrine, LabelRenderer $renderer, $class)
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
