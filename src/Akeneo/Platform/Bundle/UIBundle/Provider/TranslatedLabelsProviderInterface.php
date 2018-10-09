<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TranslatedLabelsProviderInterface
{
    /**
     * Return an array used to build HTML select
     *
     * @param array $options
     *
     * @return array
     */
    public function findTranslatedLabels(array $options = []);
}
