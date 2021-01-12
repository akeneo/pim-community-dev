import React from 'react';
import styled from "styled-components";
import {Dropdown, useBooleanState, ArrowDownIcon, ProductCategoryIllustration, TextInput, SearchIcon} from 'akeneo-design-system/lib';
const __ = require('oro/translator');
import {getColor} from 'akeneo-design-system';
import { CategoryTreeRoot } from "./CategoryTrees";

const CategoryTreeSwitcherContainer = styled(Dropdown)`
  width: 100%;
`

const CategoryTreeSwitcherButton = styled.div`
  border-bottom: 1px solid ${getColor('purple100')};
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  height: 40px;
  align-items: center;
`

const CategoryTreeSwitcherText = styled.span`
  color: ${getColor('purple100')};
`

const SearchInput = styled(TextInput)`
  border: 0;
  padding-left: 24px;
`;

const InputSearchIcon = styled(SearchIcon)`
  position: absolute;
  z-index: 1;
  top: 10px;
 
`

type CategoryTreeSwitcherProps = {
  trees: CategoryTreeRoot[];
  onClick: (treeId: number) => void;
};

/**
 * This component is faking a synchronous select2. It is not final, and should be more generic and declared in
 * shared folder.
 * This should not be reused as it, and a dedicated component should be designed.
 */
const CategoryTreeSwitcher: React.FC<CategoryTreeSwitcherProps> = ({
  trees,
  onClick,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState();
  const selectedTreeLabel = (trees.find(tree => tree.selected) || trees[0]).label;
  const [value, setValue] = React.useState<string>('');

  const filteredTrees = trees.filter(tree => {
    return tree.label.toLowerCase().includes(value.toLowerCase());
  });

  return (
  <CategoryTreeSwitcherContainer {...rest}>
    <CategoryTreeSwitcherButton onClick={open}>
      <CategoryTreeSwitcherText>{selectedTreeLabel}</CategoryTreeSwitcherText>
      <ArrowDownIcon size={20}/>
    </CategoryTreeSwitcherButton>
    {isOpen && (
      <Dropdown.Overlay verticalPosition="down" onClose={close}>
        <Dropdown.Header>
          <Dropdown.Title>
            <InputSearchIcon size={20}/>
            <SearchInput
              type='text'
              value={value}
              placeholder={__('pim_common.search')}
              onChange={setValue}
              tabIndex={1}
            />
          </Dropdown.Title>
        </Dropdown.Header>
        {filteredTrees.length ?
          <Dropdown.ItemCollection>
            {filteredTrees.map(tree =>
              <Dropdown.Item key={tree.code} onClick={() => {
                onClick(tree.id);
                close();
              }}>{tree.label}</Dropdown.Item>
            )}
          </Dropdown.ItemCollection>
          :
          <div style={{padding:'15px 40px 30px', textAlign:'center'}}>
            <ProductCategoryIllustration size={'100%'}/>
            No matches found!
          </div>
        }
      </Dropdown.Overlay>
    )}
  </CategoryTreeSwitcherContainer>
  );
};

export {CategoryTreeSwitcher};
