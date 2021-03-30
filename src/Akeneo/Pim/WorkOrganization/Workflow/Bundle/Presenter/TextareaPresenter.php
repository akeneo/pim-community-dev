<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Present textarea data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class TextareaPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::TEXTAREA === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
        return $this->explodeText($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
        return $this->explodeText($change['data']);
    }

    /**
     * Explode text into separated tags
     *
     * @param string $text
     *
     * @return array
     */
    protected function explodeText($text)
    {
        /**
         * <(\w+)[^>]*> : any opening html tag, we capture the tag name
         * (?:(?!<\/\1).)* : anything but the closing of the previously captured html tag
         * <\/\1> : closing of the captured html tag
         */
        $pattern = '/<(\w+)[^>]*>(?:(?!<\/\1).)*<\/\1>/';
        preg_match_all($pattern, $text, $matches);

        if (empty($matches[0]) || implode('', $matches[0]) !== $text) {
            return [$text];
        }

        return $matches[0];
    }
}
