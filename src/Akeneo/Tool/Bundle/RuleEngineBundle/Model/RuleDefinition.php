<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Rule definition stored in database
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RuleDefinition implements RuleDefinitionInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $type;

    /** @var string */
    protected $content;

    /** @var int */
    protected $priority = 0;

    /** @var int */
    protected $impactedSubjectCount;

    /** @var ArrayCollection */
    protected $relations;

    /** @var Collection */
    protected $translations;

    /** @var boolean */
    protected $enabled = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): RuleDefinitionInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(array $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedSubjectCount()
    {
        return $this->impactedSubjectCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setImpactedSubjectCount($impactedSubjectCount)
    {
        $this->impactedSubjectCount = $impactedSubjectCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled(bool $enabled): RuleDefinitionInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setLabel(string $locale, string $label): RuleDefinitionInterface
    {
        foreach ($this->translations as $translation) {
            /** @var $translation RuleDefinitionTranslationInterface */
            if ($translation->getLocale() === $locale) {
                $translation->setLabel($label);

                return $this;
            }
        }
        $translation = new RuleDefinitionTranslation();
        $translation->setLocale($locale);
        $translation->setLabel($label);
        $translation->setForeignKey($this);
        $this->translations->add($translation);

        return $this;
    }
}
