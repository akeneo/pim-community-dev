<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    protected $searchEngine;

    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $entityClassName;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /** @var string */
    protected $indexType;

    /**
     * @param Client        $searchEngine
     * @param ObjectManager $om
     * @param string        $entityClassName
     * @param string        $cursorClassName
     * @param int           $pageSize
     * @param string        $indexType
     */
    public function __construct(
        Client $searchEngine,
        ObjectManager $om,
        $entityClassName,
        $cursorClassName,
        $pageSize,
        $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->om = $om;
        $this->entityClassName = $entityClassName;
        $this->cursorClassName = $cursorClassName;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $options = $this->resolveOptions($options);

        $repository = $this->om->getRepository($this->entityClassName);
        if (!$repository instanceof CursorableRepositoryInterface) {
            throw InvalidObjectException::objectExpected($this->entityClassName, CursorableRepositoryInterface::class);
        }

        return new $this->cursorClassName(
            $this->searchEngine,
            $repository,
            $queryBuilder,
            $this->indexType,
            $options['page_size'],
            $options['limit'],
            $options['from']
        );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'page_size',
                'limit',
                'from',
            ]
        );
        $resolver->setDefaults(
            [
                'page_size' => $this->pageSize,
                'from' => 0
            ]
        );
        $resolver->setAllowedTypes('page_size', 'int');
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('from', 'int');

        $options = $resolver->resolve($options);

        return $options;
    }
}
