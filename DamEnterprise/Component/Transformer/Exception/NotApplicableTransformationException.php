<?php

namespace DamEnterprise\Component\Transformer\Exception;

class NotApplicableTransformationException extends \Exception
{
    public static function imageWidthTooBig($image, $transformation)
    {
        return new self(
            sprintf('Impossible to "%s" the image "%s" with a width bigger than the original.', $transformation, $image)
        );
    }

    public static function imageHeightTooBig($image, $transformation)
    {
        return new self(
            sprintf(
                'Impossible to "%s" the image "%s" with a height bigger than the original.',
                $transformation,
                $image
            )
        );
    }
}
