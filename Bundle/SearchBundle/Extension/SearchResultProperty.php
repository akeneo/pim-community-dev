<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var EntityManager */
    protected $em;

    /** @var ObjectMapper */
    protected $mapper;

    /** @var EventDispatcherInterface */
    protected $dispater;

    public function __construct(
        DateFormatExtension $dateFormatExtension,
        \Twig_Environment $environment,
        EntityManager $em,
        ObjectMapper $mapper
    ) {
        $this->em     = $em;
        $this->mapper = $mapper;

        parent::__construct($dateFormatExtension, $environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $this->getTemplate()->render(
            array(
                'indexer_item' => $record->getValue('indexer_item'),
                'entity'       => $record->getValue('entity'),
            )
        );
    }
}
