<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Number property, able to render number product attributes
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberProperty extends FieldProperty
{
    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param TranslatorInterface $translator
     * @param LocalizerInterface  $localizer
     */
    public function __construct(TranslatorInterface $translator, LocalizerInterface $localizer)
    {
        parent::__construct($translator);

        $this->localizer = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);

        return $this->localizer->localize($result, ['locale' => $this->translator->getLocale()]);
    }
}
