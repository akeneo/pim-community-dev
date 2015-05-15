<?php

namespace Context\Page\Product;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form;

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
     * @var string
     */
    protected $path = '/enrich/product/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Locales dropdown'        => ['css' => '.locale-switcher'],
                'Locales selector'        => ['css' => '#pim_product_locales'],
                'Channel dropdown'        => ['css' => '.scope-switcher'],
                'Status switcher'         => ['css' => '.status-switcher'],
                'Image preview'           => ['css' => '#lbImage'],
                'Completeness'            => ['css' => '.completeness-block'],
                'Category pane'           => ['css' => '#product-categories'],
                'Category tree'           => ['css' => '#trees'],
                'Comparison dropdown'     => ['css' => '.attribute-copy-actions'],
                'Copy selection dropdown' => ['css' => '#copy-selection-switcher'],
                'Copy translations link'  => ['css' => 'a#copy-selection'],
                'Comment threads'         => ['css' => '.comment-threads'],
            ]
        );
    }

    /**
     * @return int
     */
    public function countLocaleLinks()
    {
        return count($this->getElement('Locales dropdown')->findAll('css', 'li a'));
    }

    /**
     * @param string $locale locale code
     * @param string $locale locale label
     * @param string $flag class of the flag icon
     *
     * @return NodeElement|null
     *
     * @throws ElementNotFoundException
     */
    public function findLocaleLink($locale, $label, $flag = null)
    {
        $link = $this->getElement('Locales dropdown')->find('css', sprintf('a[data-locale="%s"]', $locale));

        if (!$link) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Locale %s link', $locale)
            );
        }

        if ($flag) {
            $flagElement = $link->find('css', 'span.flag-language i');
            if (!$flagElement) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    sprintf('Flag not found for locale %s link', $locale)
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
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $elt = $this->getElement('Locales dropdown')->find('css', '.dropdown-toggle');
        if (!$elt) {
            throw new \Exception('Could not find locale switcher.');
        }
        $elt->click();

        $elt = $this->getElement('Locales dropdown')->find('css', sprintf('a[data-locale="%s"]', $locale));
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
     * @param string $scope
     */
    public function switchScope($scope)
    {
        $elt = $this->getElement('Channel dropdown')->find('css', '.dropdown-toggle');
        if (!$elt) {
            throw new \Exception('Could not find channel dropdown.');
        }
        $elt->click();

        $elt = $this->getElement('Channel dropdown')->find('css', sprintf('a[data-scope="%s"]', $scope));
        if (!$elt) {
            throw new \Exception(sprintf('Could not find scope "%s" in switcher.', $scope));
        }
        $elt->click();
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement
     */
    public function findAvailableAttributeInGroup($attribute, $group)
    {
        return $this->find(
            'css',
            sprintf(
                'optgroup[label="%s"] option:contains("%s")',
                $group,
                $attribute
            )
        );
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
     * @param string $group
     *
     * @return NodeElement
     */
    public function getFields()
    {
        return $this->findAll('css', 'div.form-field');
    }

    /**
     * This method allows to fill a field by passing the label
     *
     * @param string  $fieldName
     * @param string  $value
     * @param Element $element
     */
    public function fillField($fieldName, $value, Element $element = null)
    {
        $fieldContainer = $this->findFieldContainer($fieldName, $element);
        $fieldType = $this->getFieldType($fieldContainer);

        switch ($fieldType) {
            case 'text':
            case 'textArea':
            case 'date':
            case 'number':
                $this->fillTextField($fieldContainer, $value);
                break;
            case 'metric':
                $this->fillMetricField($fieldContainer, $value);
                break;
            case 'multiSelect':
                $this->fillMultiSelectField($fieldContainer, $value);
                break;
            case 'price':
                $this->fillCompoundField($fieldContainer, $value);
                break;
            case 'select':
                $this->fillSelectField($fieldContainer, $value);
                break;
            default:
                parent::fillField($fieldContainer->labelContent, $value);
                break;
        }
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        $currency = null;
        if (1 === preg_match('/in (.{1,3})$/', $name)) {
            // Price in EUR
            list($name, $currency) = explode(' in ', $name);

            return $this->findCompoundField($name, $currency);
        } elseif (1 < str_word_count($name, 0, '_')) {
            // mobile Description
            $words = explode(' ', $name);
            $scope = array_shift($words);
            $name = implode(' ', $words);

            // Check that it is really a scoped field, not a field with a two word label
            if (strtolower($scope) === $scope) {
                return $this->findScopedField($name, $scope);
            }
        }

        $container = $this->find('css', sprintf('.field-container[data-attribute="%s"]', strtolower($name)));

        if (!$container) {
            throw new ElementNotFoundException($this->getSession(), 'form container ', 'value', $name);
        }

        $field = $container->find('css', 'div.field-input input');
        if (!$field) {
            $field = $container->find('css', 'div.field-input textarea');
            if (!$field) {
                throw new ElementNotFoundException($this->getSession(), 'form field ', 'id|name|label|value', $name);
            }
        }

        return $field;
    }

    /**
     * Guesses the type of field identified by $label and returns it.
     *
     * Possible identified fields are :
     * [date, metric, multiSelect, number, price, select, text, textArea]
     *
     * @param $fieldContainer
     *
     * @return string
     */
    protected function getFieldType($fieldContainer)
    {
        if (null === $fieldContainer || !$fieldContainer instanceof NodeElement) {
            return null;
        }

        $formFieldWrapper = $fieldContainer->find('css', 'div.form-field');

        if ($formFieldWrapper->hasClass('date-field')) {
            return 'date';
        } elseif ($formFieldWrapper->hasClass('metric-field')) {
            return 'metric';
        } elseif ($formFieldWrapper->hasClass('multi-select-field')) {
            return 'multiSelect';
        } elseif ($formFieldWrapper->hasClass('number-field')) {
            return 'number';
        } elseif ($formFieldWrapper->hasClass('price-collection-field')) {
            return 'price';
        } elseif ($formFieldWrapper->hasClass('simple-select-field')) {
            return 'select';
        } elseif ($formFieldWrapper->hasClass('text-field')) {
            return 'text';
        } elseif ($formFieldWrapper->hasClass('textarea-field')) {
            return 'textArea';
        } else {
            return parent::getFieldType($fieldContainer);
        }
    }

    /**
     * Find a compound field
     *
     * @param string $name
     * @param string $subLabelText
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findCompoundField($name, $value, $currency)
    {
        $container = $this->findFieldContainer($name);

        $input = $container->find('css', sprintf('input[data-currency=%s]', $currency));
        if (!$input) {
            throw new ElementNotFoundException($this->getSession(), 'compound field ', 'id|name|label|value', $name);
        }

        return $input;
    }

    protected function findFieldContainer($fieldName)
    {
        $container = $this->find('css', sprintf('.field-container[data-attribute="%s"]', strtolower($fieldName)));
        if (!$container) {
            throw new ElementNotFoundException($this->getSession(), 'field container ', 'value', $fieldName);
        }

        $container->name = $fieldName;

        return $container;
    }

    /**
     * Fills a text field element with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillTextField(NodeElement $fieldContainer, $value)
    {
        $field = $fieldContainer->find('css', 'div.field-input input');
        $field->setValue($value);
    }

    /**
     * Fills a simple select2 field with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillSelectField(NodeElement $label, $value)
    {
        if (null !== $link = $label->getParent()->getParent()->find('css', 'a.select2-choice')) {
            $link->click();

            $this->getSession()->wait(5000, '!$.active');

            // Select the value in the displayed dropdown
            if (null !== $item = $this->find('css', sprintf('#select2-drop li:contains("%s")', $value))) {
                return $item->click();
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find select2 widget inside %s', $label->getParent()->getHtml())
        );
    }

    /**
     * Fills a select2 multi-select field with $values, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     */
    protected function fillMultiSelectField(NodeElement $label, $values)
    {
        foreach ($this->listToArray($values) as $value) {
            $this->fillSelectField($label, $value);
        }
    }

    /**
     * Fills a compound field with $value, by passing the $label
     *
     * @param NodeElement $fieldContainer
     * @param string      $value
     *
     * @throws ElementNotFoundException
     */
    protected function fillCompoundField(NodeElement $fieldContainer, $value)
    {
        $amount = null;
        $currency = null;

        if (strstr($value, 'USD') || strstr($value, 'EUR')) {
            if (false !== strpos($value, ' ')) {
                list($amount, $currency) = explode(' ', $value);
            }
        }

        if (!$currency) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" field is compound but the sub label was not provided',
                    $amount
                )
            );
        }

        $field = $this->findCompoundField($fieldContainer->name, $amount, $currency);
        $field->setValue($amount);
    }

    /**
     * Fills a metric field with $value, identified by its $label.
     *
     * @param NodeElement $label
     * @param string      $value
     *
     * @throws \InvalidArgumentException
     */
    protected function fillMetricField(NodeElement $label, $value)
    {
        list($text, $select) = explode(' ', $value);
        $field = $label->getParent()->getParent()->find('css', 'div.field-input');

        $this->fillTextField($label, $text);

        if (null !== $link = $field->find('css', 'a.select2-choice')) {
            $link->click();

            $this->getSession()->wait(5000, '!$.active');

            if (null !== $item = $this->find('css', sprintf('#select2-drop li:contains("%s")', $select))) {
                return $item->click();
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find select2 widget inside %s', $field->getParent()->getHtml())
        );
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip($text)
    {
        return $this->find(
            'css',
            sprintf(
                '.validation-errors span:contains("%s")',
                $text
            )
        );
    }

    /**
     * @param string $name
     *
     * @return NodeElement[]
     */
    public function findFieldIcons($name)
    {
        $field = $this->findField($name);

        $controls = $field->getParent()->getParent();

        return $controls->findAll('css', '.field-info i');
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
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getAddOptionLinkFor($field)
    {
        return $this->findField($field)->getParent()->getParent()->getParent()->find('css', '.add-attribute-option');
    }

    /**
     * Disable a product
     *
     * @return Edit
     */
    public function disableProduct()
    {
        $el = $this->getElement('Status switcher');
        $el->find('css', 'a.dropdown-toggle')->click();
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
        $el->find('css', 'a.dropdown-toggle')->click();
        $button = $el->find('css', 'ul a[data-status="enable"]');
        if ($button) {
            $button->click();
        }

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
     * @return NodeElement
     */
    public function findCompletenessContent()
    {
        $completenessContent = $this->getElement('Completeness')->getParent();
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
        $replyBox = $this->getElement('Comment threads')->find('css', 'li.reply-to-comment');
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

            if ($actualRatio . '%' !==  $ratio) {
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
     * @param string $category
     *
     * @return CategoryView
     */
    public function selectTree($category)
    {
        $link = $this->getElement('Category pane')->find('css', sprintf('#trees-list li a:contains("%s")', $category));
        if (!$link) {
            throw new \InvalidArgumentException(sprintf('Tree "%s" not found', $category));
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
        $this->getElement('Comparison dropdown')->find('css', 'div.start-copying')->click();
        $localeSwitcher = $this
            ->getElement('Comparison dropdown')
            ->find('css', 'div.locale-switcher');
        $localeSwitcher->find('css', 'a.dropdown-toggle')->click();
        $languages = $localeSwitcher
            ->findAll('css', 'ul.dropdown-menu li a');

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
     * Find a completeness cell from channel and locale codes
     *
     * @param string $channelCode (channel code)
     * @param string $localeCode  (locale code)
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function findCompletenessCell($channelCode, $localeCode)
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
