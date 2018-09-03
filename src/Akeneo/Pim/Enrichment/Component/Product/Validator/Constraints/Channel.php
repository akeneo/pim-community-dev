<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Choice;

/**
 * Channel constraint annotation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class Channel extends Choice
{
    public $message = 'The channel you selected does not exist.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_at_least_a_channel';
    }
}
