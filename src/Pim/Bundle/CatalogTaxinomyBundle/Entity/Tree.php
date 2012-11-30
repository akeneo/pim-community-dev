<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Test class for Tree entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="tree")
 * @ORM\Entity
 */
class Tree
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
     * @ORM\Column(name="left", type="integer")
     */
    protected $left;

    /**
     * @var integer $level
     *
     * @ORM\Column(name="level", type="integer")
     */
    protected $level;

    /**
     * @var integer $right
     *
     * @ORM\Column(name="right", type="integer")
     */
    protected $right;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    protected $type;

    /**
     * @var Tree $parent
     *
     * @ORM\ManyToOne(targetEntity="Tree", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

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
     * @return Tree
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
     * @return Tree
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
     * @return Tree
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
     * @return Tree
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
     * Set position
     * @param integer $position
     *
     * @return Tree
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set type
     * @param string $type
     *
     * @return Tree
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
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parent
     * @param Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree $parent
     *
     * @return Tree
     */
    public function setParent(\Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Pim\Bundle\CatalogTaxinomyBundle\Entity\Tree
     */
    public function getParent()
    {
        return $this->parent;
    }
}