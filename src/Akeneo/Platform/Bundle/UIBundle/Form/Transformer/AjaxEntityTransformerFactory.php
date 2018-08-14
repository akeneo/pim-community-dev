<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Ajax choice transformer factory
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerFactory implements TransformerFactoryInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $class
     */
    public function __construct(RegistryInterface $doctrine, $class)
    {
        $this->doctrine = $doctrine;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        $repository = $this->doctrine->getRepository($options['class']);

        return new $this->class($repository, $options);
    }
}
