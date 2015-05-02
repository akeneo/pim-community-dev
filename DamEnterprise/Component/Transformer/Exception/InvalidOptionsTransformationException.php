<?php

namespace DamEnterprise\Component\Transformer\Exception;

class InvalidOptionsTransformationException extends NotApplicableTransformationException
{
    public static function general(\Exception $e, $transformation)
    {
        return new self(
            sprintf('Your options does not fulfil the requirements of the "%s" transformation.', $transformation),
            $e->getCode(),
            $e
        );
    }

    public static function chooseOneOption(array $options, $transformation)
    {
        $options = '"' . implode('", "', $options) . '"';

        return new self(
            sprintf('Please choose one of the among option %s for the "%s" transformation.', $options, $transformation)
        );
    }

    public static function ratio($option, $transformation)
    {
        return new self(
            sprintf('The option "%s" of the "%s" transformation should be between 0 and 1.', $option, $transformation)
        );
    }
}
