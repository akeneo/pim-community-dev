<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

/**
 * Present text data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class TextPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('text', $change);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $this->explodeText($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $this->explodeText($change['text']);
    }

    /**
     * Explode text into separated paragraphs
     *
     * @param string $text
     *
     * @return array
     */
    protected function explodeText($text)
    {
        preg_match_all('/<p>(.*?)<\/p>/', $text, $matches);

        return !empty($matches[0]) ? $matches[0] : [$text];
    }
}
