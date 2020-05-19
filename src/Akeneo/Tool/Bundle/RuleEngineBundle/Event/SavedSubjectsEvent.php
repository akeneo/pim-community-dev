<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Event;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

class SavedSubjectsEvent extends RuleEvent
{
    /** @var array */
    private $subjects;

    public function __construct(RuleDefinitionInterface $definition, array $subjects)
    {
        parent::__construct($definition);
        $this->subjects = $subjects;
    }

    public function getSubjects(): array
    {
        return $this->subjects;
    }
}
