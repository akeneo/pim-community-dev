<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Job\RefreshProjectCompleteness;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RefreshProjectCompletenessJobParameter implements
    DefaultValuesProviderInterface,
    ConstraintCollectionProviderInterface
{
    /** @var string */
    protected $jobName;

    /**
     * @param $jobName
     */
    public function __construct($jobName)
    {
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'product_identifier' => new NotBlank(),
                'locale_identifier'  => new NotBlank(),
                'channel_identifier' => new NotBlank(),
                'user_to_notify'     => new Type('string'),
                'is_user_authenticated' => new Type('bool'),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return [
            'user_to_notify' => null,
            'is_user_authenticated' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return $this->jobName === $job->getName();
    }
}
