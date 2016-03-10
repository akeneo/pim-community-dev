<?php

namespace Context\Page\Product;

use Akeneo\Component\Classification\Model\Category;
use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Page\Base\ProductEditForm;
use Context\Page\Category\CategoryView;

/**
 * Product edit page
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends ProductEditForm
{
    /**
     * @var string
     */
    protected $path = '/enrich/product/{id}';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Copy locales dropdown'   => ['css' => '.attribute-copy-actions .locale-switcher'],
                'Locales selector'        => ['css' => '#pim_product_locales'],
                'Copy channel dropdown'   => ['css' => '.attribute-copy-actions .scope-switcher'],
                'Copy source dropdown'    => ['css' => '.attribute-copy-actions .source-switcher'],
                'Status switcher'         => ['css' => '.status-switcher'],
                'Image preview'           => ['css' => '#lbImage'],
                'Completeness'            => ['css' => '.completeness-block'],
                'Category pane'           => ['css' => '#product-categories'],
                'Category tree'           => [
                    'css'        => '#trees',
                    'decorators' => [
                        'Pim\Behat\Decorator\TreeDecorator\JsTreeDecorator'
                    ]
                ],
                'Category tree selector'           => [
                    'css'        => '#trees-list',
                    'decorators' => [
                        'Pim\Behat\Decorator\TreeSelectorDecorator\ListDecorator'
                    ]
                ],
                'Copy actions'            => ['css' => '.copy-actions'],
                'Comment threads'         => ['css' => '.comment-threads'],
                'Meta zone'               => ['css' => '.baseline > .meta'],
                'Modal'                   => ['css' => '.modal'],
                'Progress bar'            => ['css' => '.progress-bar'],
                'Save'                    => ['css' => 'button.save-product'],
                'Attribute tab'           => [
                    'css'        => '.tab-container .product-attributes',
                    'decorators' => [
                        'Pim\Behat\Decorator\TabDecorator\ComparableTabDecorator'
                    ]
                ],
                'Comparison panel' => [
                    'css'        => '.tab-container .attribute-actions .attribute-copy-actions',
                    'decorators' => [
                        'Pim\Behat\Decorator\ContextSwitcherDecorator',
                        'Pim\Behat\Decorator\TabElementDecorator\ComparisonPanelDecorator'
                    ]
                ],
                'Main context selector' => [
                    'css'        => '.tab-container .product-attributes .attribute-edit-actions .context-selectors',
                    'decorators' => [
                        'Pim\Behat\Decorator\ContextSwitcherDecorator'
                    ]
                ]
            ]
        );
    }

    /**
     * Press the save button
     */
    public function save()
    {
        $element = $this->getElement('Save');

        $this->spin(function () use ($element) {
            return $element->isVisible();
        }, "Waiting for save button to be visible");

        $element->click();

        $this->spin(function () {
            return null === $this->find(
                'css',
                '*:not(.hash-loading-mask):not(.grid-container):not(.loading-mask) > .loading-mask'
            );
        });
    }

    /**
     * @param bool $copy search in copy panel or main panel
     *
     * @return int
     */
    public function countLocaleLinks($copy = false)
    {
        return count(
            $this->getElement($copy ? 'Copy locales dropdown' : 'Locales dropdown')
                ->findAll('css', 'a[data-locale]')
        );
    }

    /**
     * @param string $localeCode locale code
     * @param string $label      locale label
     * @param string $flag       class of the flag icon
     * @param bool   $copy       search in copy panel or main panel
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement|null
     */
    public function findLocaleLink($localeCode, $label = null, $flag = null, $copy = false)
    {
        $dropdown = $this->getElement($copy ? 'Copy locales dropdown' : 'Locales dropdown');
        $dropdown->find('css', '.dropdown-toggle')->click();
        $link = $dropdown->find('css', sprintf('a[data-locale="%s"]', $localeCode));

        if (!$link) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Locale %s link', $localeCode)
            );
        }

        if ($flag) {
            $flagElement = $link->find('css', 'span.flag-language i');
            if (!$flagElement) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    sprintf('Flag not found for locale %s link', $localeCode)
                );
            }
            if (strpos($flagElement->getAttribute('class'), $flag) === false) {
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
     * @return array
     */
    public function getHistoryRows()
    {
        return $this->findAll('css', '.product-version');
    }

    /**
     * @return int
     */
    public function getFieldsCount()
    {
        return count($this->findAll('css', 'div.form-field'));
    }

    /**
     * @return NodeElement
     */
    public function getFields()
    {
        return $this->findAll('css', 'div.form-field');
    }

    /**
     * @param string $attribute
     *
     * @return int
     */
    public function getAttributePosition($attribute)
    {
        $productValues = $this->spin(function () {
            return $this->find('css', '.tab-pane.active.product-values');
        }, "Spining on find for product-values tab to get attribute position");

        $rows = $this->spin(function () use ($productValues) {
            return $productValues->findAll('css', '.field-container');
        }, "Spining on findAll for rows on product-values to get attribute position");

        $position = $this->spin(function () use ($rows, $attribute) {
            foreach ($rows as $index => $row) {
                if ($row->find('css', sprintf(':contains("%s")', $attribute))) {
                    return $index + 1;
                }
            }
        }, "Spining on scanning rows to get attribute position");

        if (!$position) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Attribute "%s" not found', $attribute)
            );
        }

        return $position;
    }

    /**
     * Extracts and returns the label NodeElement, identified by $field content and $element
     *
     * @param string  $field
     * @param Element $element
     *
     * @return NodeElement
     */
    protected function extractLabelElement($field, $element)
    {
        $subLabelContent = null;
        $labelContent    = $field;

        if (strstr($field, 'USD') || strstr($field, 'EUR')) {
            if (false !== strpos($field, ' ')) {
                list($subLabelContent, $labelContent) = explode(' ', $field);
            }
        }

        if ($element) {
            $label = $this->spin(function () use ($element, $labelContent) {
                return $element->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('unable to find label %s in element : %s', $labelContent, $element->getHtml()));
        } else {
            $label = $this->spin(function () use ($labelContent) {
                return $this->find('css', sprintf('label:contains("%s")', $labelContent));
            }, sprintf('unable to find label %s', $labelContent));
        }

        if (!$label) {
            $label = new \StdClass();
        }

        $label->labelContent    = $labelContent;
        $label->subLabelContent = $subLabelContent;

        return $label;
    }

    /**
     * @param string $label
     *
     * @return NodeElement[]
     */
    public function findFieldIcons($label)
    {
        $field = $this->findFieldContainer($label);

        return $field->findAll('css', 'i[class*="icon-"]');
    }

    /**
     * @param string $name
     *
     * @return NodeElement[]
     */
    public function findFieldFooter($name)
    {
        $field = $this->findField($name);

        return $field->getParent()->getParent()
            ->find('css', 'footer')->find('css', '.footer-elements-container');
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        $link = $this->spin(function () use ($field) {
            $link = $this->find(
                'css',
                sprintf(
                    '.control-group:contains("%s") .remove-attribute',
                    $field
                )
            );

            if (!$link) {
                $link = $this->find(
                    'css',
                    sprintf(
                        '.field-container:contains("%s") .remove-attribute',
                        $field
                    )
                );
            }

            return $link;
        }, "Spining to get remove link on product edit form for field $field");

        return $link;
    }

    /**
     * Get the "remove file" button for a media file (trash icon)
     *
     * @param $field
     *
     * @return NodeElement|null
     */
    public function getRemoveFileButtonFor($field)
    {
        try {
            $button = $this->spin(function () use ($field) {
                return $this->find('css', sprintf('.field-container:contains("%s") .clear-field', $field));
            });
        } catch (\Exception $e) {
            $button = null;
        }

        return $button;
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getAddOptionLinkFor($field)
    {
        $fieldContainer = $this->findFieldContainer($field);

        return $fieldContainer->find('css', '.add-attribute-option');
    }

    /**
     * Disable a product
     *
     * @return Edit
     */
    public function disableProduct()
    {
        $el = $this->getElement('Status switcher');

        $statusToggle = $this->spin(function () use ($el) {
            return $el->find('css', 'a.dropdown-toggle');
        }, "Spining to get the status dropdown toggle to disable product on PEF");

        $statusToggle->click();
        $button = $el->find('css', 'ul a[data-status="disable"]');
        if ($button) {
            $button->click();
        }

        return $this;
    }

    /**
     * Enable a product
     *
     * @return Edit
     */
    public function enableProduct()
    {
        $el = $this->getElement('Status switcher');

        $statusToggle = $this->spin(function () use ($el) {
            return $el->find('css', 'a.dropdown-toggle');
        }, "Spining to get the status dropdown toggle to enable product on PEF");

        $statusToggle->click();
        $button = $el->find('css', 'ul a[data-status="enable"]');
        if ($button) {
            $button->click();
        }

        return $this;
    }

    /**
     * @return NodeElement|null
     */
    public function getStatusSwitcher()
    {
        try {
            $switcher = $this->spin(function () {
                return $this->find('css', '.status-switcher');
            });
        } catch (\Exception $e) {
            $switcher = null;
        }

        return $switcher;
    }

    /**
     * @return NodeElement|null
     */
    public function getImagePreview()
    {
        $preview = $this->getElement('Image preview');

        if (!$preview || false === strpos($preview->getAttribute('style'), 'display: block')) {
            return null;
        }

        return $preview;
    }

    /**
     * Get the completeness content
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function findCompletenessContent()
    {
        return $this->getElement('Completeness', 'Completeness content not found !!!');
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
        $replyBox = $this->getElement('Comment threads')->find('css', 'li.reply-to-comment');
        if (!$replyBox) {
            throw new \LogicException('Comment reply box not found !');
        }

        $textarea = $replyBox->find('css', 'textarea');
        if (!$textarea) {
            throw new \LogicException('Comment reply textarea not found !');
        }

        $textarea->setValue($message);

        $this->spin(function () use ($replyBox) {
            $replyBox->find('css', '.send-comment')->click();

            return true;
        });
    }

    /**
     * Get the comment threads node
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement|mixed
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
     * @return NodeElement|mixed
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
     * @return NodeElement the comment
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
        $link = $comment->find('css', 'span.remove-comment');
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
     *
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
            ->find('css', 'div.progress');

        if (!$completenessCell) {
            throw new \InvalidArgumentException(
                sprintf('No progress found for %s:%s', $channelCode, $localeCode)
            );
        }

        if ("" === $state) {
            if ($completenessCell->find('css', 'div.progress')) {
                throw new \InvalidArgumentException(
                    sprintf('No progress bar should be visible for %s:%s', $channelCode, $localeCode)
                );
            }
        } else {
            if (!$completenessCell->find('css', sprintf('div.progress-%s', $state))) {
                throw new \InvalidArgumentException(
                    sprintf('Progress bar is not %s for %s:%s', $state, $channelCode, $localeCode)
                );
            }
        }
    }

    /**
     * Check completeness message
     *
     * @param string $channelCode
     * @param string $localeCode
     * @param string $info
     *
     * @throws \InvalidArgumentException
     */
    public function checkCompletenessMissingValues($channelCode, $localeCode, $info)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode)
            ->find('css', 'div.missing');

        if ($info === '') {
            if ($completenessCell->find('css', 'span')) {
                throw new \InvalidArgumentException(
                    sprintf('Expected to find no missing values for %s:%s', $channelCode, $localeCode)
                );
            }
        } else {
            $infoPassed = explode(' ', $info);
            foreach ($infoPassed as $value) {
                $found = $completenessCell->find('css', sprintf('span[data-attribute="%s"]', $value));
                if (!$found) {
                    throw new \InvalidArgumentException(
                        sprintf('Missing value %s not found for %s:%s', $value, $channelCode, $localeCode)
                    );
                }
            }
        }
    }

    /**
     * Check completeness ratio
     *
     * @param string $channelCode
     * @param string $localeCode
     * @param string $ratio
     *
     * @throws \InvalidArgumentException
     */
    public function checkCompletenessRatio($channelCode, $localeCode, $ratio)
    {
        $completenessCell = $this
            ->findCompletenessCell($channelCode, $localeCode);

        if ("" === $ratio) {
            if (is_object($completenessCell->find('css', 'div.bar'))) {
                throw new \InvalidArgumentException(
                    sprintf('Ratio should not be found for %s:%s', $channelCode, $localeCode)
                );
            }
        } elseif ($ratio !== '') {
            $actualRatio = $completenessCell
                ->find('css', 'div.bar')
                ->getAttribute('data-ratio');

            if ($actualRatio . '%' !== $ratio) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected to find ratio %s for %s:%s, found %s%%',
                        $ratio,
                        $channelCode,
                        $localeCode,
                        $actualRatio
                    )
                );
            }
        }
    }

    /**
     * Find legend div
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
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
     * @param Category $category
     *
     * @throws \Exception
     */
    public function clickCategoryFilterLink($category)
    {
        $node = $this
            ->getCategoryTree()
            ->find('css', sprintf('#node_%s a', $category->getId()));

        if (null === $node) {
            throw new \Exception(sprintf('Could not find category filter "%s".', $category->getId()));
        }

        $node->click();
    }

    /**
     * Filter by unclassified products
     */
    public function clickUnclassifiedCategoryFilterLink()
    {
        $node = $this
            ->getCategoryTree()
            ->find('css', '#node_-1 a');

        if (null === $node) {
            throw new \Exception(sprintf('Could not find unclassified category filter.'));
        }

        $node->click();
    }

    /**
     * Change the family of the current product
     *
     * @param string $family
     */
    public function changeFamily($family)
    {
        $changeLink = $this->spin(function () {
            return $this->getElement('Meta zone')->find('css', '.product-family > .change-family');
        });

        $changeLink->click();

        $selectContainer = $this->spin(function () {
            return $this->getElement('Modal')->find('css', '.select2-container');
        });

        $this->fillSelectField($selectContainer, $family);

        $validationButton = $this->spin(function () {
            return $this->find('css', '.modal .btn.ok');
        });

        $validationButton->click();

        return $this->spin(function () use ($family) {
            return $this->getElement('Meta zone')->find('css', '.product-family .product-family')->getHTML();
        });
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function waitForProgressionBar()
    {
        $this->spin(function () {
            return $this->getElement('Progress bar');
        });
    }

    /**
     * Find a completeness cell from channel and locale codes
     *
     * @param string $channelCode (channel code)
     * @param string $localeCode  (locale code)
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    public function findCompletenessCell($channelCode, $localeCode)
    {
        $completenessTable = $this->findCompletenessContent();

        $locale = $completenessTable
            ->find('css', sprintf('span.locale[data-locale="%s"]', $localeCode));

        if (!$locale) {
            throw new \Exception(sprintf('Could not find completeness for locale "%s".', $localeCode));
        }

        $cell = $locale
            ->getParent()
            ->getParent()
            ->find('css', sprintf('span.channel[data-channel="%s"]', $channelCode));

        if (!$cell) {
            throw new \Exception(sprintf('Could not find completeness for channel "%s".', $channelCode));
        }

        return $cell->getParent();
    }

    /**
     * Find an attribute group in the nav
     *
     * @param string $attributeGroupCode
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    public function getAttributeGroupTab($group)
    {
        $groups = $this->getElement('Form Groups');

        $groupNode = $this->spin(function () use ($groups, $group) {
            return $groups->find('css', sprintf('.group-label:contains("%s")', $group));
        }, sprintf("Can't find attribute group '%s'", $group));

        return $groupNode->getParent()->getParent();
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

    /**
     * @return NodeElement|null
     */
    public function getCategoryTree()
    {
        $modal = $this->find('css', '.modal');
        if (null !== $modal && $modal->isVisible() && null !== $tree = $modal->find('css', '#tree')) {
            return $tree;
        }

        return $this->getElement('Category tree');
    }

    /**
     * @return NodeElement
     */
    public function getSaveAndBackButton()
    {
        $submit = $this->spin(function () {
            return $this->find('css', '.submit-form');
        }, 'Submit button not found');

        $submit->find('css', '.dropdown-toggle')->click();

        $dropdownMenu = $submit->find('css', '.dropdown-menu');

        return $this->spin(function () use ($dropdownMenu) {
            return $dropdownMenu->find('css', '.save-product-and-back');
        }, '"Save and back" button not found');
    }
}
