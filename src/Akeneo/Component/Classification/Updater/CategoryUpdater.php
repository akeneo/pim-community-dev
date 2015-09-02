<?php

namespace Akeneo\Component\Classification\Updater;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates a category.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryUpdater implements ObjectUpdaterInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $categoryRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->accessor           = PropertyAccess::createPropertyAccessor();
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($category, array $data, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Component\Classification\Model\CategoryInterface", "%s" provided.',
                    ClassUtils::getClass($category)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($category, $field, $value);
        }

        return $this;
    }

    /**
     * @param CategoryInterface $category
     * @param string            $field
     * @param mixed             $data
     */
    protected function setData(CategoryInterface $category, $field, $data)
    {
        // TODO: Locales should be managed in a dedicated implementation of this updater
        if ('labels' === $field && $category instanceof TranslatableInterface) {
            foreach ($data as $localeCode => $label) {
                $category->setLocale($localeCode);
                $translation = $category->getTranslation();
                $translation->setLabel($label);
            }
        } elseif ('parent' === $field) {
            $categoryParent = $this->findParent($data);
            if (null !== $categoryParent) {
                $category->setParent($categoryParent);
            } else {
                throw new \InvalidArgumentException(sprintf('The parent category "%s" does not exist', $data));
            }
        } else {
            $this->accessor->setValue($category, $field, $data);
        }
    }

    /**
     * @param string $code
     *
     * @return CategoryInterface|null
     */
    protected function findParent($code)
    {
        return $this->categoryRepository->findOneByIdentifier($code);
    }
}
