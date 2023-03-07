import React, {FC} from 'react';
import {CategoryTreeCode, CategoryTreeRoot, useTranslate} from '@akeneo-pim-community/shared';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useCategoryTrees} from '../hooks';

type CategoryTreeSwitcherProps = {
  value?: CategoryTreeCode;
  onChange: (categoryTreeRoot: CategoryTreeRoot) => void;
};

const CategoryTreeSwitcher: FC<CategoryTreeSwitcherProps> = ({value, onChange}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const trees = useCategoryTrees(onChange);

  const handleChange = (tree: CategoryTreeRoot) => {
    close();
    onChange(tree);
  };

  return (
    <Dropdown>
      <SwitcherButton label={translate('pim_identifier_generator.selection.settings.categories.tree')} onClick={open}>
        {trees.find(({code}) => code === value)?.label}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.categories')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {trees.map(tree => (
              <Dropdown.Item isActive={tree.code === value} key={tree.code} onClick={() => handleChange(tree)}>
                {tree.label}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {CategoryTreeSwitcher};
