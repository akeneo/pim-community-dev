<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var EntityManager */
    protected $em;

    /** @var ObjectMapper */
    protected $mapper;

    /**
     * @param \Twig_Environment $environment
     * @param EntityManager $em
     * @param ObjectMapper $mapper
     */
    public function __construct(\Twig_Environment $environment, EntityManager $em, ObjectMapper $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;

        parent::__construct($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $item = new ResultItem(
            $this->em,
            $record->getValue('entity_name'),
            $record->getValue('record_id'),
            null,
            null,
            null,
            $this->mapper->getEntityConfig($record->getValue('entity_name'))
        );

        return $this->getTemplate()->render(['item' => $item]);
    }
}
