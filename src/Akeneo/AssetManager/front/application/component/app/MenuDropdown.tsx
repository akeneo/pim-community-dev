import * as React from 'react';
import {Dropdown, IconButton, MoreVerticalIcon, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Tab} from 'akeneoassetmanager/application/configuration/sidebar';

type MenuDropdownProps = {
  tabs: Tab[];
  label: string;
  onTabChange: (element: Tab) => void;
};

const MenuDropdown = ({tabs, label, onTabChange}: MenuDropdownProps) => {
  const [isOpen, open, close] = useBooleanState();
  const translate = useTranslate();

  return (
    <Dropdown>
      <IconButton
        icon={<MoreVerticalIcon />}
        level="tertiary"
        ghost="borderless"
        title={translate(label)}
        onClick={open}
      />
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate(label)}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {tabs.map(menuItem => (
              <Dropdown.Item
                key={menuItem.code}
                onClick={() => {
                  onTabChange(menuItem);
                  close();
                }}
              >
                {translate(menuItem.label)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {MenuDropdown};
