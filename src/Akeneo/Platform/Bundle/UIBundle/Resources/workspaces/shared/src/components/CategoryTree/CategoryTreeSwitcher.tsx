import React from 'react';
import styled, {css} from 'styled-components';
import {
  Dropdown,
  useBooleanState,
  ArrowDownIcon,
  ProductCategoryIllustration,
  TextInput,
  SearchIcon,
  getColor,
  AkeneoThemedProps,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTreeRoot} from './CategoryTrees';

const CategoryTreeSwitcherContainer = styled(Dropdown)`
  width: 100%;
`;

const CategoryTreeSwitcherButton = styled.div`
  border-bottom: 1px solid ${getColor('brand', 100)};
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  height: 40px;
  align-items: center;
`;

const CategoryTreeSwitcherText = styled.span`
  color: ${getColor('brand', 100)};
`;

const SearchInput = styled(TextInput)`
  border: 0;
  padding-left: 24px;
`;

const InputSearchIcon = styled(SearchIcon)`
  position: absolute;
  z-index: 1;
  top: 10px;
`;

const EmptyResultsContainer = styled.div`
  padding: 15px 40px 30px;
  text-align: center;
`;

const DropdownItem = styled.span<{$selected: boolean} & AkeneoThemedProps>`
  ${({$selected}) =>
    $selected &&
    css`
      color: ${getColor('brand', 100)};
    `}
`;

type CategoryTreeSwitcherProps = {
  trees: CategoryTreeRoot[];
  onClick: (treeId: number) => void;
};

/**
 * This component is faking a synchronous select2. It is not final, and should be more generic and declared in
 * shared folder.
 * This should not be reused as it, and a dedicated component should be designed.
 */
const CategoryTreeSwitcher: React.FC<CategoryTreeSwitcherProps> = ({trees, onClick, ...rest}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const selectedTreeLabel = (trees.find(tree => tree.selected) || trees[0])?.label;
  const [value, setValue] = React.useState<string>('');

  const filteredTrees = trees.filter(tree => {
    return tree.label.toLowerCase().includes(value.toLowerCase());
  });

  return (
    <CategoryTreeSwitcherContainer {...rest}>
      <CategoryTreeSwitcherButton onClick={open} aria-haspopup="listbox">
        <CategoryTreeSwitcherText>{selectedTreeLabel}</CategoryTreeSwitcherText>
        <ArrowDownIcon size={20} />
      </CategoryTreeSwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>
              <InputSearchIcon size={20} />
              <SearchInput
                type="text"
                value={value}
                placeholder={translate('pim_common.search')}
                onChange={setValue}
                tabIndex={1}
              />
            </Dropdown.Title>
          </Dropdown.Header>
          {filteredTrees.length ? (
            <Dropdown.ItemCollection role="listbox">
              {filteredTrees.map(tree => (
                <Dropdown.Item
                  role="option"
                  key={tree.code}
                  onClick={() => {
                    onClick(tree.id);
                    close();
                  }}
                >
                  <DropdownItem $selected={tree.selected}>{tree.label}</DropdownItem>
                </Dropdown.Item>
              ))}
            </Dropdown.ItemCollection>
          ) : (
            <EmptyResultsContainer>
              <ProductCategoryIllustration size={'100%'} />
              {translate('pim_common.select2.no_match')}
            </EmptyResultsContainer>
          )}
        </Dropdown.Overlay>
      )}
    </CategoryTreeSwitcherContainer>
  );
};

export {CategoryTreeSwitcher};
