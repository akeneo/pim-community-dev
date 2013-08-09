<?php

namespace Pim\Bundle\ConfigBundle\Helper;

class ChoiceViewHelper
{
    public static function reorder(array $values)
    {
        uasort(
            $values,
            function ($a, $b) {
                if ($a->label === $b->label) {
                    return 0;
                }

                return ($a->label < $b->label) ? -1 : 1;
            }
        );

        return $values;
    }
}
