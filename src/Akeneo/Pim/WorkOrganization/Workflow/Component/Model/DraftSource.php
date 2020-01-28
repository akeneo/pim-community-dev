<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class DraftSource
{
    /**
     * @var string
     */
    private $source;
    /**
     * @var string
     */
    private $sourceLabel;
    /**
     * @var string
     */
    private $author;
    /**
     * @var string
     */
    private $authorLabel;

    public function __construct(string $source, string $sourceLabel, string $author, string $authorLabel)
    {
        $this->source = $source;
        $this->sourceLabel = $sourceLabel;
        $this->author = $author;
        $this->authorLabel = $authorLabel;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getSourceLabel(): string
    {
        return $this->sourceLabel;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getAuthorLabel(): string
    {
        return $this->authorLabel;
    }
}
