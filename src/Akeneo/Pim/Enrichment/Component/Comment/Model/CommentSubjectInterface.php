<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Model;

/**
 * Comment subject interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommentSubjectInterface
{
    /**
     * @return string|int
     */
    public function getId();
}
