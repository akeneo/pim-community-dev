<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var EntityManager */
    protected $em;

    /** @var ObjectMapper */
    protected $mapper;

    public function __construct(
        DateFormatExtension $dateFormatExtension,
        TranslatorInterface $translator,
        \Twig_Environment $environment,
        EntityManager $em,
        ObjectMapper $mapper
    ) {
        $this->em     = $em;
        $this->mapper = $mapper;

        parent::__construct($dateFormatExtension, $translator, $environment);
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
