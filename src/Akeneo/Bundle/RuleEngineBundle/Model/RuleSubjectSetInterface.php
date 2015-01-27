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
interface RuleSubjectSetInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return RuleSubjectSetInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $type
     *
     * @return RuleSubjectSetInterface
     */
    public function setType($type);

    /**
     * @return CursorInterface
     */
    public function getSubjectsCursor();

    /**
     * @param CursorInterface $subjectsCursor
     *
     * @return RuleSubjectSetInterface
     */
    public function setSubjectsCursor(CursorInterface $subjectsCursor);
}
