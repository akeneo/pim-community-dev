<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Model;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Subjects set that will be impacted by a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleSubjectSet implements RuleSubjectSetInterface
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $type;

    /** @var CursorInterface */
    protected $subjectsCursor;

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
    public function setCode($code)
    {
        $this->code = $code;

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
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectsCursor()
    {
        return $this->subjectsCursor;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectsCursor(CursorInterface $subjectsCursor)
    {
        $this->subjectsCursor = $subjectsCursor;

        return $this;
    }
}
