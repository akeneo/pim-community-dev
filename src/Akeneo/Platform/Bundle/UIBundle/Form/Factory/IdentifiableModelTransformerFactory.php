<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Factory;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifiableModelTransformerFactory
{
    /** @var array */
    protected $className;

    /**
     * @param string $className Transformer class to create
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Creates a transformer for the given options
     *
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $options
     */
    public function create(IdentifiableObjectRepositoryInterface $repository, array $options): DataTransformerInterface
    {
        return new $this->className($repository, $options);
    }
}
