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
 * Rule interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface RuleDefinitionInterface
{
    /**
     * @return int
     */
    public function getId();

    public function setId(int $id): RuleDefinitionInterface;

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return RuleDefinitionInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return RuleDefinitionInterface
     */
    public function setType($type);

    /**
     * Get rule content. In default implementation, the content
     * is stored in JSON but is transformed to array when loaded.
     *
     * @return array
     */
    public function getContent();

    /**
     * @param array $content
     *
     * @return RuleDefinitionInterface
     */
    public function setContent(array $content);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return RuleDefinitionInterface
     */
    public function setPriority($priority);

    /**
     * @return int
     */
    public function getImpactedSubjectCount();

    /**
     * @param int $impactedSubjectCount
     *
     * @return RuleDefinitionInterface
     */
    public function setImpactedSubjectCount($impactedSubjectCount);

    /**
     * @param ArrayCollection $relations
     *
     * @return RuleDefinitionInterface
     */
    public function setRelations($relations);

    /**
     * @return ArrayCollection
     */
    public function getRelations();

    /**
     * @return Collection of RuleDefinitionTranslationInterface
     */
    public function getTranslations(): Collection;

    public function setLabel(string $locale, string $label): RuleDefinitionInterface;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): RuleDefinitionInterface;
}
