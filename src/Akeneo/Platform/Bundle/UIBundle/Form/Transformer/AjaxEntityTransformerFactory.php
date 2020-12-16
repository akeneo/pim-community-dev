<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Ajax choice transformer factory
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTransformerFactory implements TransformerFactoryInterface
{
    protected ManagerRegistry $doctrine;
    protected string $class;

    public function __construct(ManagerRegistry $doctrine, $class)
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
