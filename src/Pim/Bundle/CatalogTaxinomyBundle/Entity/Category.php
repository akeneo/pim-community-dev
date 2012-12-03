<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * Category entity class implementing tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="Akeneo_PimCatalogTaxinomy_Category")
 * @ORM\Entity(repositoryClass="CategoryRepository")
 */
class Category
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=64)
     */
    protected $title;

    /**
     * @var integer $left
     *
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $left;

    /**
     * @var integer $level
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $level;

    /**
     * @var integer $right
     *
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $right;

    /**
     * @var integer $root
     *
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @var Category $parent
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    protected $type;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->title    = '';
        $this->type     = 'default';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     * @param string $title
     *
     * @return Category
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set left
     * @param integer $left
     *
     * @return Category
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Get left
     *
     * @return integer
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set level
     * @param integer $level
     *
     * @return Category
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set right
     * @param integer $right
     *
     * @return Category
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Get right
     *
     * @return integer
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set root
     * @param integer $root
     *
     * @return Category
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set parent
     * @param Category $parent
     *
     * @return Category
     */
    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     * @param Category $children
     *
     * @return Category
     */
    public function addChildren(Category $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $children
     */
    public function removeChildren(\Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Has children
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set type
     * @param string $type
     *
     * @return Category
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     *
     * TODO : Must be cleaned in pre-persist or otherwise
     */
    public function getType()
    {
        if ($this->getLevel() === 1) {
            return 'drive';
        } else if ($this->hasChildren()) {
            return 'folder';
        } else {
            return 'default';
        }
    }
}