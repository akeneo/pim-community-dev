import React, {FC, useState, useEffect} from 'react';
import {CategoryTreeCode, CategoryTreeRoot, useRouter} from '@akeneo-pim-community/shared';
import {Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';

type CategoryTreeSwitcherProps = {
  value?: CategoryTreeCode;
  onChange: (categoryTreeCode: CategoryTreeCode) => void;
};

const CategoryTreeSwitcher: FC<CategoryTreeSwitcherProps> = ({
  value,
  onChange,
}) => {
  const Router = useRouter();
  const [isOpen, open, close] = useBooleanState();
  const [trees, setTrees] = useState<CategoryTreeRoot[]>([]);

  useEffect(() => {
    const url = Router.generate('pim_enrich_categorytree_listtree', {
      _format: 'json',
      dataLocale: undefined,
      include_sub: 0,
      context: 'view',
    });

    fetch(url).then(response => {
      response.json().then(json => {
        const trees: CategoryTreeRoot[] = json.map((tree: any) => {
          return {...tree, selected: tree.selected === 'true'};
        });
        setTrees(trees);
        const currentTree = trees.find(tree => tree.selected);
        if (currentTree) {
          onChange(currentTree.code);
        }
      });
    });
  }, []);

  const handleChange = (treeCode: CategoryTreeCode) => {
    close();
    onChange(treeCode)
  };

  return <Dropdown>
    <SwitcherButton label={'Tree'} onClick={open}>
      {trees.find(tree => tree.code === value)?.label}
    </SwitcherButton>
    {isOpen && <Dropdown.Overlay onClose={close}>
      <Dropdown.Header>
        <Dropdown.Title>
          Categories
        </Dropdown.Title>
      </Dropdown.Header>
      <Dropdown.ItemCollection>
        {trees.map(tree => <Dropdown.Item
          isActive={tree.code === value}
          key={tree.code}
          onClick={() => handleChange(tree.code)}
        >
          {tree.label}
        </Dropdown.Item>)}
      </Dropdown.ItemCollection>
    </Dropdown.Overlay>}
  </Dropdown>;
};

export {CategoryTreeSwitcher};
