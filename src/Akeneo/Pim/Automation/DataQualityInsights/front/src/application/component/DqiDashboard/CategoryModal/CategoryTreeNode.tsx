import React, {FunctionComponent, useState} from "react";
import useFetchCategoryChildren from "../../../../infrastructure/hooks/useFetchCategoryChildren";

interface CategoryTreeNodeProps {
  categoryId: string;
  categoryLabel: string;
  locale: string;
  isOpened?: boolean;
  categoryCode: string;
  onSelectCategory: (categoryCode: string, categoryLabel: string, categoryId: string, rootCategoryId: string) => void;
  hasChildren: boolean;
  selectedCategories: string[];
  withCheckbox: boolean;
  rootCategoryId: string;
}

const CategoryTreeNode: FunctionComponent<CategoryTreeNodeProps> = ({categoryId, categoryLabel, locale, categoryCode, onSelectCategory, hasChildren, selectedCategories, withCheckbox, isOpened = false, rootCategoryId}) => {

  const [isOpen, setIsOpen] = useState<boolean>(isOpened);

  categoryId = categoryId.replace('node_', '');
  const children = useFetchCategoryChildren(locale, categoryId, isOpen);

  categoryLabel = categoryLabel ? categoryLabel : '[' + categoryCode + ']';

  return (
    <li className={`jstree-root jstree-last ${hasChildren ? (isOpen ? 'jstree-open' : 'jstree-closed') : 'jstree-leaf'} ${selectedCategories.includes(categoryCode) ? 'jstree-checked' : 'jstree-unchecked'}`}>
      <ins className="jstree-icon" onClick={() => setIsOpen(!isOpen)}>
        &nbsp;
      </ins>
      <a href="#" onClick={(event) => event.preventDefault()}>
        {withCheckbox && (
          <ins className="jstree-checkbox" onClick={() => onSelectCategory(categoryCode, categoryLabel, categoryId, rootCategoryId)}>&nbsp;</ins>
        )}
        <ins className="jstree-icon">
          &nbsp;
        </ins>
        <span onClick={() => onSelectCategory(categoryCode, categoryLabel, categoryId, rootCategoryId)}>{categoryLabel}</span>
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
                selectedCategories={selectedCategories}
                withCheckbox={withCheckbox}
                rootCategoryId={rootCategoryId}
              />
            )
          })}
        </ul>
      )}
    </li>
  )
};

export default CategoryTreeNode;
