<?php

namespace Akeneo\Tool\Component\Localization\Presenter;

/**
 * Boolean presenter, able to render booleans readable for a human.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanPresenter implements PresenterInterface
{
    /** @var string[] */
    protected $allowedFields;

    /**
     * @param string[] $allowedFields
     */
    public function __construct(array $allowedFields)
    {
        $this->allowedFields = $allowedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        if (in_array($value, [true, 'true', '1', 1], true)) {
            return 'true';
        }
        if (in_array($value, [false, 'false', '0', 0], true)) {
            return 'false';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($fieldCode)
    {
        return in_array($fieldCode, $this->allowedFields);
    }
}
