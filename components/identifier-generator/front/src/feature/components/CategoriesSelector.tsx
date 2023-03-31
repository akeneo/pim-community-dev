import React, {FC, useCallback, useMemo, useState} from 'react';
import {CategoryCode, CategoryTree, CategoryTreeRoot, CategoryValue, useTranslate} from '@akeneo-pim-community/shared';
import {Dropdown, TagInput, useBooleanState} from 'akeneo-design-system';
import {CategoryTreeSwitcher} from './CategoryTreeSwitcher';
import {Styled} from './Styled';
import {useCategoryLabels, useCategoryTree} from '../hooks';

type CategoriesSelectorProps = {
  categoryCodes: CategoryCode[];
  onChange: (categoryCodes: CategoryCode[]) => void;
};

const CategoriesSelector: FC<CategoriesSelectorProps> = ({categoryCodes, onChange}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [currentTree, setCurrentTree] = useState<CategoryTreeRoot | undefined>(undefined);
  const categoryLabels = useCategoryLabels(categoryCodes);

  const filledCategoryLabels = useMemo(() => {
    return categoryCodes.reduce((categoryCodes, categoryCode) => {
      if (categoryLabels[categoryCode] !== null) {
        categoryCodes[categoryCode] = categoryLabels[categoryCode];
      }

      return categoryCodes;
    }, {});
  }, [categoryCodes, categoryLabels]);

  const invalidValue = useMemo(() => {
    return categoryCodes.filter(categoryCode => categoryLabels[categoryCode] === null);
  }, [categoryCodes, categoryLabels]);

  const {init, childrenCallback} = useCategoryTree(currentTree);

  /* istanbul ignore next */
  const isCategorySelected = useCallback(
    (category: CategoryValue) => categoryCodes.includes(category.code),
    [categoryCodes]
  );

  /* istanbul ignore next */
  const handleChange = useCallback(
    (value: string, checked: boolean) => {
      onChange(checked ? [...categoryCodes, value] : categoryCodes.filter(code => code !== value));
    },
    [categoryCodes, onChange]
  );

  return (
    <Styled.CategoriesDropdownContainer>
      <TagInput
        value={categoryCodes}
        onChange={onChange}
        onFocus={open}
        labels={filledCategoryLabels}
        invalidValue={invalidValue}
      />
      {isOpen && (
        <Dropdown.Overlay onClose={close} horizontalPosition={'left'}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.categories')}</Dropdown.Title>
            <CategoryTreeSwitcher onChange={setCurrentTree} value={currentTree?.code} />
          </Dropdown.Header>
          {currentTree && (
            <Styled.CategoryTreeContainer>
              <CategoryTree
                categoryTreeCode={currentTree.code}
                init={init}
                childrenCallback={childrenCallback}
                isCategorySelected={isCategorySelected}
                onChange={handleChange}
              />
            </Styled.CategoryTreeContainer>
          )}
        </Dropdown.Overlay>
      )}
    </Styled.CategoriesDropdownContainer>
  );
};

export {CategoriesSelector};
