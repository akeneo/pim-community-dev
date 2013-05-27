<?php

namespace Oro\Bundle\JsFormValidationBundle\Generator;

use Symfony\Component\Validator\Mapping\GetterMetadata;
use APY\JsFormValidationBundle\Generator\GettersLibraries as BaseGettersLibraries;

class GettersLibraries extends BaseGettersLibraries
{
    /**
     * Gets Bundle name using entity reference.
     *
     * Fixed bug in parent method implementation for cases when class name is Foo\Bundle\BarBundle\Entity\Baz.
     *
     * @param GetterMetadata $getterMetadata
     * @return null|string
     */
    public function getBundle(GetterMetadata $getterMetadata)
    {
        $allBundles = $this->container->getParameter('kernel.bundles');
        $className = $getterMetadata->getClassName();
        $result = null;

        if (preg_match('/.+\\\\([^\\\\]+Bundle)(|[\\\\].+)$/', $className, $matches)) {
            if (isset($allBundles[$matches[1]])) {
                $result = $matches[1];
            } else {
                $chunks = explode(chr(92), $className);
                if (!empty($chunks[2]) && $chunks[1] == 'Bundle' && isset($allBundles[$chunks[0] . $chunks[2]])) {
                    $result = $chunks[0] . $chunks[2];
                } elseif (!empty($chunks[1])) {
                    $result = $chunks[0] . $chunks[1];
                }
            }
        }

        return $result;
    }
}
