import React, {FC, useState} from 'react';
import {
  CategoryCode,
  CategoryTree,
  CategoryTreeModel,
  CategoryTreeRoot,
  CategoryValue,
  ParentCategoryTree,
  useRouter,
  parseResponse,
  CategoryResponse,
} from '@akeneo-pim-community/shared';
import {Dropdown, TagInput, useBooleanState} from 'akeneo-design-system';
import {CategoryTreeSwitcher} from './CategoryTreeSwitcher';
import {Styled} from './Styled';

type CategoriesSelectorProps = {
  categoryCodes: CategoryCode[];
  onChange: (categoryCodes: CategoryCode[]) => void;
};

const CategoriesSelector: FC<CategoriesSelectorProps> = ({
  categoryCodes,
  onChange
}) => {
  const Router = useRouter();
  const [isOpen, open, close] = useBooleanState();
  const [currentTree, setCurrentTree] = useState<CategoryTreeRoot | undefined>(undefined);
  const handleTreeChange = (tree: CategoryTreeRoot) => {
    setCurrentTree(tree);
  };

  const getChildrenUrl = (id: number) => {
    return Router.generate('pim_enrich_categorytree_children', {
      _format: 'json',
      id,
    });
  };

  const init = async () => {
    if (currentTree) {
      const response = await fetch(getChildrenUrl(currentTree.id));
      const json: CategoryResponse[] = await response.json();

      return {
        id: currentTree.id,
        code: currentTree.code,
        label: currentTree.label,
        selectable: false,
        children: json.map(child =>
          parseResponse(child, {
            selectable: true,
          })
        ),
      };
    }
    throw new Error('Not possible');
  };

  const childrenCallback: (id: number) => Promise<CategoryTreeModel[]> = async id => {
    const response = await fetch(getChildrenUrl(id));
    const json: CategoryResponse[] = await response.json();

    return json.map(child =>
      parseResponse(child, {
        selectable: true,
      })
    );
  };

  const isCategorySelected: (category: CategoryValue, _: ParentCategoryTree) => boolean = category => {
    return categoryCodes.includes(category.code);
  };

  const handleChange = (value: string, checked: boolean) => {
    if (checked) {
      onChange([...categoryCodes, value]);
    } else {
      onChange(categoryCodes.filter(code => code !== value));
    }
  };

  return <Dropdown>
    <TagInput value={categoryCodes} onChange={onChange} onFocus={open} labels={{}} />
    {isOpen && <Dropdown.Overlay onClose={close} horizontalPosition={'left'} fullWidth={true}>
      <Dropdown.Header>
        <Dropdown.Title>
            Categories
        </Dropdown.Title>
        <CategoryTreeSwitcher onChange={handleTreeChange} value={currentTree?.code} />
      </Dropdown.Header>
      {currentTree && <Styled.CategoryTreeContainer>
        <CategoryTree
          categoryTreeCode={currentTree.code}
          init={init}
          childrenCallback={childrenCallback}
          isCategorySelected={isCategorySelected}
          onChange={handleChange}
      />
      </Styled.CategoryTreeContainer>
      }
    </Dropdown.Overlay>}
  </Dropdown>;
};

export {CategoriesSelector};
