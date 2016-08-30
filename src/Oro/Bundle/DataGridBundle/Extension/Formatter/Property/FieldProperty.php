<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FieldProperty extends AbstractProperty
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        if ($this->getOr(self::FRONTEND_TYPE_KEY) === self::TYPE_SELECT) {
            $translator = $this->translator;

            $choices = $this->getOr('choices', []);
            $translated = array_map(
                function ($item) use ($translator) {
                    return $translator->trans($item);
                },
                $choices
            );

            $this->params['choices'] = $translated;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->getOr(self::DATA_NAME_KEY, $this->get(self::NAME_KEY)));
        } catch (\LogicException $e) {
            // default value
            $value = null;
        }

        return $value;
    }
}
