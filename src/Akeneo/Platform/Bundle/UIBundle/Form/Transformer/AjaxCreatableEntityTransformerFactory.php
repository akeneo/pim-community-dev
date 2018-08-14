<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Ajax creatable entity transformer factory
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxCreatableEntityTransformerFactory
{
    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Creates a transformer for the given options
     *
     * @param SaverInterface $saver       Saver for the creatable entity
     * @param array          $options
     * @param string         $transformer Transformer class for the creatable entity
     *
     * @return DataTransformerInterface
     */
    public function create(SaverInterface $saver, array $options, $transformerClass)
    {
        $repository = $this->doctrine->getRepository($options['class']);

        return new $transformerClass($saver, $repository, $options);
    }
}
