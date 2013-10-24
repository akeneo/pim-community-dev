<?php
namespace Oro\Bundle\SearchBundle\Extension\Formatter;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var EntityManager */
    protected $em;

    /** @var ObjectMapper */
    protected $mapper;

    /**
     * @var ResultFormatter
     */
    protected $formatter;

    /**
     * @param \Twig_Environment $environment
     * @param EntityManager $em
     * @param ObjectMapper $mapper
     * @param ResultFormatter $formatter
     */
    public function __construct(
        \Twig_Environment $environment,
        EntityManager $em,
        ObjectMapper $mapper,
        ResultFormatter $formatter
    ) {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->formatter = $formatter;

        parent::__construct($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $this->formatter->getResultEntities(array($record));

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
