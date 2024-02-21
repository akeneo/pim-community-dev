<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

/**
 * Abstract field adder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFieldAdder implements FieldAdderInterface
{
    /** @var array */
    protected $supportedFields = [];

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }
}
