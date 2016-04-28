<?php

namespace Pim\Component\Enrich\Repository;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChoicesProviderInterface
{
    /**
     * Return an array sued to build HTML select
     *
     * @param array $options
     *
     * @return array
     */
    public function findChoices(array $options = []);
}
