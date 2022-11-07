<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectLocale extends Constraint
{
    /** @var string */
    public $message = 'teamwork_assistant.project.project_locale';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'project_locale_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
