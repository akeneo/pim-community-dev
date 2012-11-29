<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

/**
 * Transform XML content to a list of category entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoriesXmlToCategoriesTransformer implements TransformInterface
{
    /**
     * Xml element to parse
     * @var \SimpleXMLElement
     */
    protected $simpleDoc;

    /**
     * List of category entities
     * @var array
     */
    protected $categories;

    /**
     * @staticvar array
     */
    protected static $langs = array('en_US' => 1);

    /**
     * Constructor
     * @param \SimpleXMLElement $simpleDoc
     */
    public function __construct(\SimpleXMLElement $simpleDoc)
    {
        $this->simpleDoc = $simpleDoc;
        $this->categories = array();
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        foreach ($this->simpleDoc->Response->CategoriesList->Category as $xmlCategory) {
            // create category entity
            $icecatId = (string) $xmlCategory['ID'];
            $category = $this->createCategory($icecatId, $xmlCategory);

            // create parent category entity
            $xmlParent      = $xmlCategory->ParentCategory;
            $icecatParentId = (string) $xmlParent['ID'];
            $parent         = $this->createCategory($icecatParentId, $xmlParent->Names);

            // add parent
            $category->setParent($parent);

            // add category to list
            $this->categories[$icecatId]       = $category;
            $this->categories[$icecatParentId] = $parent;
        }

        return $this->categories;
    }

    /**
     * Create a category entity
     * @param string            $icecatId        icecat id
     * @param \SimpleXMLElement $xmlElementNames title of the category
     *
     * @return Category
     */
    protected function createCategory($icecatId, \SimpleXMLElement $xmlElementNames)
    {
        // get category if already exists else instanciate new
        if (isset($this->categories[$icecatId])) {
            $category = $this->categories[$icecatId];
        } else {
            $category = new Category();
        }

        // set translatable title
        foreach ($xmlElementNames as $name) {
            if (in_array((integer) $name['langid'], self::$langs)) {
                $title = isset($name['Value']) ? $name['Value'] : $name;
                $category->setTitle($title);
            }
        }

        return $category;
    }
}
