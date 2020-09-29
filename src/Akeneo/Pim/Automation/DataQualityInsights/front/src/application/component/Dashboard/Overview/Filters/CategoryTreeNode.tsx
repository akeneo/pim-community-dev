import React, {FunctionComponent, useState} from "react";
import useFetchCategoryChildren from "../../../../../infrastructure/hooks/Dashboard/useFetchCategoryChildren";

interface CategoryTreeNodeProps {
  categoryId: string;
  categoryLabel: string;
  locale: string;
  isOpened?: boolean;
  categoryCode: string;
  onSelectCategory: (categoryCode: string, categoryLabel: string) => void;
  hasChildren: boolean;
  selectedCategory: string | null;
}

const CategoryTreeNode: FunctionComponent<CategoryTreeNodeProps> = ({categoryId, categoryLabel, locale, categoryCode, onSelectCategory, hasChildren, selectedCategory, isOpened = false}) => {

  const [isOpen, setIsOpen] = useState<boolean>(isOpened);

  const children = useFetchCategoryChildren(locale, categoryId.replace('node_', ''), isOpen);

  return (
    <li className={`jstree-root jstree-last ${hasChildren ? (isOpen ? 'jstree-open' : 'jstree-closed') : 'jstree-leaf'} ${selectedCategory === categoryCode ? 'jstree-checked' : ''}`}>
      <ins className="jstree-icon" onClick={() => setIsOpen(!isOpen)}>
        &nbsp;
      </ins>
      <a href="#" onClick={(event) => event.preventDefault()}>
        <ins className="jstree-icon">
          &nbsp;
        </ins>
        <span onClick={() => onSelectCategory(categoryCode, categoryLabel)}>{categoryLabel ? categoryLabel : '[' + categoryCode + ']'}</span>
      </a>
      {isOpen && hasChildren && (
        <ul>
          {children.hasOwnProperty('children') && Object.values(children.children).map((category: any, index: number) => {
            return (
              <CategoryTreeNode
                key={index}
                categoryId={category.attr.id}
                categoryLabel={category.data}
                locale={locale}
                categoryCode={category.attr['data-code']}
                onSelectCategory={onSelectCategory}
                hasChildren={category.state !== "leaf"}
                selectedCategory={selectedCategory}
              />
            )
          })}
        </ul>
      )}
    </li>
  )
};

export default CategoryTreeNode;
