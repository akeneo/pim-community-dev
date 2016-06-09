<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\Writer;

use Pim\Component\Connector\Writer\Database\BaseWriter;

/**
 * Accesses rights writer
 *
 * @author Arnaud Langlade <arnaud.lanlade@akeneo.com>
 */
class AccessesWriter extends BaseWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $objects)
    {
        foreach ($objects as $object) {
            parent::write($object);
        }
    }
}
