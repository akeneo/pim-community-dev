<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

/**
 * Abstract field copier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFieldCopier implements FieldCopierInterface
{
    /** @var array */
    protected $supportedFromFields = [];

    /** @var array */
    protected $supportedToFields = [];

    /**
     * @param array $supportedFromFields
     * @param array $supportedToFields
     */
    public function __construct(array $supportedFromFields, array $supportedToFields)
    {
        $this->supportedFromFields = $supportedFromFields;
        $this->supportedToFields   = $supportedToFields;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFields($fromField, $toField)
    {
        $supportsFromField = in_array($fromField, $this->supportedFromFields);
        $supportsToField   = in_array($toField, $this->supportedToFields);

        return $supportsFromField && $supportsToField;
    }
}
