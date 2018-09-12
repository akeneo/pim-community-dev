<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Job;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Renders the job type
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TypeProperty extends FieldProperty
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        return $this->translator->trans(sprintf('pim_datagrid.cells.type.%s', $value));
    }
}
