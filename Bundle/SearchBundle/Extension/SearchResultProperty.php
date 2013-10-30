<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;

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
        return $this->getTemplate()->render(
            array(
                'indexer_item' => $record->getValue('indexer_item'),
                'entity'       => $record->getValue('entity'),
            )
        );
    }
}
