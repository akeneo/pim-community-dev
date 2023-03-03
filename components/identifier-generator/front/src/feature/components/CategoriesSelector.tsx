import React, {FC, useState} from 'react';
import {CategoryCode, CategoryTreeCode} from '@akeneo-pim-community/shared';
import {Dropdown, TagInput, useBooleanState} from 'akeneo-design-system';
import {CategoryTreeSwitcher} from './CategoryTreeSwitcher';

type CategoriesSelectorProps = {
  categoryCodes: CategoryCode[];
  onChange: (categoryCodes: CategoryCode[]) => void;
};

const CategoriesSelector: FC<CategoriesSelectorProps> = ({
  categoryCodes,
  onChange
}) => {
  const [isOpen, open, close] = useBooleanState();
  const [currentTreeCode, setCurrentTreeCode] = useState<CategoryTreeCode | undefined>(undefined);
  const handleTreeChange = (treeCode: CategoryTreeCode) => {
    setCurrentTreeCode(treeCode);
  }

  return <Dropdown>
    <TagInput value={categoryCodes} onChange={onChange} onFocus={open}/>
    {isOpen && <Dropdown.Overlay onClose={close} horizontalPosition={'left'}>
        <Dropdown.Header>
            <Dropdown.Title>
                Categories
            </Dropdown.Title>
            <CategoryTreeSwitcher onChange={handleTreeChange} value={currentTreeCode} />
        </Dropdown.Header>
        This is the tree
    </Dropdown.Overlay>}
  </Dropdown>;
};

export {CategoriesSelector};
