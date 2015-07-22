<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Product edit page
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string $path
     */
    protected $path = '/enrich/product/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Locales dropdown'        => array('css' => '#locale-switcher'),
                'Locales selector'        => array('css' => '#pim_product_locales'),
                'Enable switcher'         => array('css' => '#switch_status'),
                'Image preview'           => array('css' => '#lbImage'),
                'Completeness'            => array('css' => '#pim_enrich-product-tab-completeness'),
                'Category pane'           => array('css' => '#pim_enrich-product-tab-category'),
                'Category tree'           => array('css' => '#trees'),
                'Comparison dropdown'     => array('css' => '#comparison-switcher'),
                'Copy selection dropdown' => array('css' => '#copy-selection-switcher'),
                'Copy translations link'  => array('css' => 'a#copy-selection'),
                'Comment threads'         => array('css' => '#comment_threads'),
            )
        );
    }

    /**
     * @return integer
     */
    public function countLocaleLinks()
    {
        return count($this->getElement('Locales dropdown')->findAll('css', 'a'));
    }

    /**
     * @param string $locale
     * @param string $content
     *
     * @return NodeElement|null
     */
    public function findLocaleLink($locale, $content = null)
    {
        $link = $this->getElement('Locales dropdown')->findLink($locale);

        if (!$link) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Locale %s link', $locale)
            );
        }

        if ($content) {
            if (strpos($link->getText(), $content) === false) {
                return null;
            }
        }

        return $link;
    }

    /**
     * @param string $language
     */
    public function selectLanguage($language)
    {
        $this->getElement('Locales selector')->selectOption(ucfirst($language), true);
    }

    /**
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $elt = $this->getElement('Locales dropdown')->find('css', '.dropdown-toggle');
        if (!$elt) {
            throw new \Exception('Could not find locale switcher.');
        }
        $elt->click();

        $elt = $this->getElement('Locales dropdown')->find('css', sprintf('a[title="%s"]', $locale));
        if (!$elt) {
            throw new \Exception(sprintf('Could not find locale "%s" in switcher.', $locale));
        }
        $elt->click();
    }

    /**
     * @param string $locale
     * @param string $label
     *
     * @return NodeElement
     */
    public function findLocale($locale, $label)
    {
        return $this->getElement('Locales dropdown')->find(
            'css',
            sprintf(
                'a:contains("%s"):contains("%s")',
                strtoupper($locale),
                $label
            )
        );
    }

    /**
     * @param string $group
     *
     * @return integer
     */
    public function getFieldsCountFor($group)
    {
        return count($this->getFieldsForGroup($group));
    }

    /**
     * @param string $group
     *
     * @return NodeElement
     */
    public function getFieldsForGroup($group)
    {
        $locator = sprintf('#tabs-%s label', $group instanceof AttributeGroup ? $group->getId() : 0);

        return $this->findAll('css', $locator);
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        $currency = null;
        if (1 === preg_match('/in ((?:.){1,3})$/', $name)) {
            // Price in EUR
            list($name, $currency) = explode(' in ', $name);

            return $this->findPriceField($name, $currency);
        } elseif (1 < str_word_count($name)) {
            // mobile Description
            $words = explode(' ', $name);
            $scope = array_shift($words);
            $name = implode(' ', $words);

            // Check that it is really a scoped field, not a field with a two word label
            if (strtolower($scope) === $scope) {
                return $this->findScopedField($name, $scope);
            }
        }
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException($this->getSession(), 'form field ', 'id|name|label|value', $name);
        }

        return $field;
    }

    /**
     * @param string $name
     *
     * @return NodeElement[]
     */
    public function findFieldIcons($name)
    {
        $controls = $this->findField($name);
        do {
            $controls = $controls->getParent();
        } while (null !== $controls && !$controls->hasClass('controls'));

        return $controls->findAll('css', '.icons-container i');
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        return $this->find('css', sprintf('.control-group:contains("%s") .remove-attribute', $field));
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getAddOptionLinkFor($field)
    {
        return $this->find('css', sprintf('.control-group:contains("%s") .add-attribute-option', $field));
    }

    /**
     * Disable a product
     *
     * @return Edit
     */
    public function disableProduct()
    {
        $this->getElement('Enable switcher')->click();

        return $this;
    }

    /**
     * Enable a product
     *
     * @return Edit
     */
    public function enableProduct()
    {
        $this->getElement('Enable switcher')->click();

        return $this;
    }

    /**
     * @return NodeElement|void
     */
    public function getImagePreview()
    {
        $preview = $this->getElement('Image preview');

        if (!$preview || false === strpos($preview->getAttribute('style'), 'display: block')) {
            return;
        }

        return $preview;
    }

    /**
     * Get the completeness content
     *
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCompletenessContent()
    {
        $completenessContent = $this->getElement('Completeness')->find('css', 'table#progress-table');
        if (!$completenessContent) {
            throw new \InvalidArgumentException('Completeness content not found !!!');
        }

        return $completenessContent;
    }

    /**
     * @param string $message
     *
     * @throws \LogicException
     */
    public function createComment($message)
    {
        $textarea = $this->getElement('Comment threads')->find('css', 'li.comment-create textarea');
        if (!$textarea) {
            throw new \LogicException('Comment creation box not found !');
        }

        $textarea->click();
        $textarea->setValue($message);
        $this->getElement('Comment threads')->pressButton('Add a new comment');
    }

    /**
     * @param NodeElement $comment
     * @param string      $message
     *
     * @throws \LogicException
     */
    public function replyComment(NodeElement $comment, $message)
    {
        $comment->click();
        $replyBox = $this->getElement('Comment threads')->find('css', 'li.comment-reply-action.toggle-active');
        if (!$replyBox) {
            throw new \LogicException('Comment reply box not found !');
        }

        $textarea = $replyBox->find('css', 'textarea');
        if (!$textarea) {
            throw new \LogicException('Comment reply textarea not found !');
        }

        $textarea->setValue($message);
        $replyBox->pressButton('Reply');
    }

    /**
     * Get the comment threads node
     *
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement|mixed
     */
    protected function findCommentTopics()
    {
        return $this->getElement('Comment threads')->findAll('css', 'li.comment-topic');
    }

    /**
     * Get the comment replies node
     *
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement|mixed
     */
    protected function findCommentReplies()
    {
        return $this->getElement('Comment threads')->findAll('css', 'li.comment-reply');
    }

    /**
     * @param string $message
     * @param string $author
     *
     * @throws \LogicException in case the comment does not exist
     *
     * @return NodeElement     the comment
     */
    public function findComment($message, $author)
    {
        $comments = array_merge($this->findCommentTopics(), $this->findCommentReplies());
        if (empty($comments)) {
            throw new \InvalidArgumentException('No comment nodes found !');
        }

        $columnIdx = null;
        foreach ($comments as $index => $thread) {
            if (null !== $currentMessage = $this->findCommentMessage($thread)) {
                $currentMessage = $currentMessage->getText();
            }
            if (null !== $currentAuthor = $this->findCommentAuthor($thread)) {
                $currentAuthor = $currentAuthor->getText();
            }

            /*
            if (null !== $currentDate = $this->findCommentDate($thread)) {
                $currentDate = preg_replace('/[^a-zA-Z0-9 -]/', ' ', $currentDate->getText());
                if (false !== $atIdx = strpos($currentDate, 'at')) {
                    $currentDate = trim(substr($currentDate, 0, $atIdx));
                }
            }
            */

            if ($currentMessage === $message && $currentAuthor === $author) {
                $columnIdx = $index;
                break;
            }
        }

        if (null === $columnIdx) {
            throw new \LogicException(
                sprintf('Comment "%s" from "%s" not found.', $message, $author)
            );
        }

        return $comments[$columnIdx];
    }

    /**
     * @param NodeElement $comment
     *
     * @throws \LogicException
     */
    public function deleteComment(NodeElement $comment)
    {
        $link = $comment->find('css', 'a.comment-delete-dialog');
        if (null === $link) {
            throw new \LogicException(
                sprintf('Delete link of comment "%s" not found.', $comment->getText())
            );
        }
        $link->click();
    }

    /**
     * @param NodeElement $reply
     * @param NodeElement $comment
     *
     * @return bool
     */
    public function isReplyOfComment(NodeElement $reply, NodeElement $comment)
    {
        return $reply->getParent()->getText() === $comment->getParent()->getText();
    }

    /**
     * @param NodeElement $element
     *
     * @return NodeElement|mixed|null
     */
    protected function findCommentAuthor(NodeElement $element)
    {
        return $element->find('css', 'span.author');
    }

    /**
     * @param NodeElement $element
     *
     * @return NodeElement|mixed|null
     */
    protected function findCommentDate(NodeElement $element)
    {
        return $element->find('css', 'span.date');
    }

    /**
     * @param NodeElement $element
     *
     * @return NodeElement|mixed|null
     */
    protected function findCommentMessage(NodeElement $element)
    {
        return $element->find('css', 'span.message');
    }

    /**
     * Check completeness state
     * @param string $channelCode
     * @param string $localeCode
     * @param string $state
     *
     * @throws \InvalidArgumentException
     */
    public function checkCompletenessState($channelCode, $localeCode, $state)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode)
            ->find('css', 'div.progress-cell');

        if ("" === $state) {
            if ($completenessCell->find('css', 'div.bar')) {
                throw new \InvalidArgumentException(
                    sprintf('No progress bar should be visible for %s:%s', $state, $channelCode, $localeCode)
                );
            }
        } else {
            if (!$completenessCell->find('css', sprintf('div.bar-%s', $state))) {
                throw new \InvalidArgumentException(
                    sprintf('Progress bar is not %s for %s:%s', $state, $channelCode, $localeCode)
                );
            }
        }
    }

    /**
     * Check completeness message
     * @param string $channelCode
     * @param string $localeCode
     * @param string $info
     *
     * @throws \InvalidArgumentException
     */
    public function checkCompletenessMessage($channelCode, $localeCode, $info)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode)
            ->find('css', 'div.progress-cell');

        if ($info === "Not yet calculated") {
            if ($info != $completenessCell->getText()) {
                throw new \InvalidArgumentException(
                    sprintf('Message %s not found for %s:%s', $info, $channelCode, $localeCode)
                );
            }
        } elseif ($info !== "none") {
            $infoPassed = ($info === 'Complete')
                ? ($completenessCell->getText() === $info)
                : $completenessCell->find('css', sprintf('span.progress-info:contains("%s")', $info));
            if (!$infoPassed) {
                throw new \InvalidArgumentException(
                    sprintf('Message %s not found for %s:%s', $info, $channelCode, $localeCode)
                );
            }
        }
    }

    /**
     * Check completeness ratio
     * @param string $channelCode
     * @param string $localeCode
     * @param string $ratio
     *
     * @throws \InvalidArgumentException
     */
    public function checkCompletenessRatio($channelCode, $localeCode, $ratio)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode)
            ->find('css', 'div.progress-cell');

        if ("" === $ratio) {
            if (is_object($completenessCell->find('css', 'div.progress'))) {
                throw new \InvalidArgumentException(
                    sprintf('Ratio should not be found for %s:%s', $channelCode, $localeCode)
                );
            }
        } elseif ($ratio !== 'none') {
            $title = $completenessCell
                ->find('css', 'div.progress')
                ->getAttribute('data-original-title');

            $pattern = sprintf('/^%s complete/', $ratio);
            if (!$title || preg_match($pattern, $title) !== 1) {
                throw new \InvalidArgumentException(
                    sprintf('Ratio %s not found for %s:%s', $ratio, $channelCode, $localeCode)
                );
            }
        }
    }

    /**
     * Find legend div
     * @throws \InvalidArgumentException
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCompletenessLegend()
    {
        $legend = $this->getElement('Completeness')->find('css', 'div#legend');
        if (!$legend) {
            throw new \InvalidArgumentException('Legend content not found !!!');
        }

        return $legend;
    }

    /**
     * @param string $category
     *
     * @return CategoryView
     */
    public function selectTree($category)
    {
        $link = $this->getElement('Category pane')->find('css', sprintf('#trees-list li a:contains("%s")', $category));
        if (!$link) {
            throw new\InvalidArgumentException(sprintf('Tree "%s" not found', $category));
        }
        $link->click();

        return $this;
    }

    /**
     * @param string $category
     *
     * @return CategoryView
     */
    public function expandCategory($category)
    {
        $category = $this->findCategoryInTree($category)->getParent();
        if ($category->hasClass('jstree-closed')) {
            $category->getParent()->find('css', 'ins')->click();
        }

        return $this;
    }

    /**
     * @param string $category
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function findCategoryInTree($category)
    {
        $elt = $this->getElement('Category tree')->find('css', sprintf('li a:contains("%s")', $category));
        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Unable to find category "%s" in the tree', $category));
        }

        return $elt;
    }

    /**
     * Find comparison language labels
     *
     * @return string[]
     */
    public function getComparisonLanguages()
    {
        $this->getElement('Comparison dropdown')->find('css', 'button[data-toggle="dropdown"]')->click();
        $languages = $this->getElement('Comparison dropdown')->findAll('css', 'ul.dropdown-menu li .title');

        return array_map(
            function ($language) {
                return $language->getText();
            },
            $languages
        );
    }

    /**
     * @param string $language
     *
     * @throws \InvalidArgumentException
     */
    public function compareWith($language)
    {
        $this->getElement('Comparison dropdown')->find('css', 'button:contains("Translate")')->click();
        if (!in_array($language, $this->getComparisonLanguages())) {
            throw new \InvalidArgumentException(
                sprintf('Language "%s" is not available for comparison', $language)
            );
        }

        $this->getElement('Comparison dropdown')->find(
            'css',
            sprintf('ul.dropdown-menu a:contains("%s")', $language)
        )->click();
    }

    /**
     * Automatically select translations given the specified mode
     *
     * @param string $mode
     */
    public function autoSelectTranslations($mode)
    {
        $this
            ->getElement('Copy selection dropdown')
            ->find('css', 'button:contains("Select")')
            ->click();

        $selector = $this
            ->getElement('Copy selection dropdown')
            ->find('css', sprintf('a:contains("%s")', $mode));

        if (!$selector) {
            throw new \InvalidArgumentException(sprintf('Translation copy mode "%s" not found', $mode));
        }

        $selector->click();
    }

    /**
     * Manually select translation given the specified field label
     *
     * @param string $field
     */
    public function manualSelectTranslation($field)
    {
        $this
            ->find('css', sprintf('tr:contains("%s") .comparisonSelection', $field))
            ->check();
    }

    /**
     * Click the link to copy selected translations
     */
    public function copySelectedTranslations()
    {
        $this->getElement('Copy translations link')->click();
    }

    /**
     * Find a completeness cell from column and row (channel and locale codes)
     * @param string $columnCode (channel code)
     * @param string $rowCode    (locale code)
     *
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function findCompletenessCell($columnCode, $rowCode)
    {
        $completenessTable = $this->findCompletenessContent();

        $columnIdx = 0;
        foreach ($completenessTable->findAll('css', 'thead th') as $index => $header) {
            if ($header->getText() === $columnCode) {
                $columnIdx = $index;
                break;
            }
        }
        if ($columnIdx === 0) {
            throw new \InvalidArgumentException(sprintf('Column %s not found', $columnCode));
        }

        $cells = $completenessTable->findAll('css', sprintf('tbody tr:contains("%s") td', $rowCode));
        if (!$cells || count($cells) < $columnIdx) {
            throw new \InvalidArgumentException(sprintf('Row %s not found', $rowCode));
        }

        return $cells[$columnIdx];
    }

    /**
     * @param string $name
     * @param string $scope
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findScopedField($name, $scope)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }

        $scopeLabel = $label
            ->getParent()
            ->find('css', sprintf('label[title="%s"]', $scope));

        if (!$scopeLabel) {
            throw new ElementNotFoundException($this->getSession(), 'form label', 'title', $name);
        }

        return $this->find('css', sprintf('#%s', $scopeLabel->getAttribute('for')));
    }
}
