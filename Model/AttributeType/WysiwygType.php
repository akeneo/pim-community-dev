<?php
namespace Pim\Bundle\ProductBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;

/**
 * Extends TextArea attribute type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class WysiwygType extends TextAreaType
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->formType = 'pim_wysiwyg';
    }
}
