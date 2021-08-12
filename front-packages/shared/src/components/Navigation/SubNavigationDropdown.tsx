import {Dropdown, IconButton, MoreVerticalIcon, useBooleanState} from 'akeneo-design-system';
import React, {FC} from 'react';
import {useRouter, useTranslate} from '../../hooks';
import {SubNavigationEntry} from './SubNavigation';

type Props = {
  title?: string;
  entries: SubNavigationEntry[];
};

const SubNavigationDropdown: FC<Props> = ({entries, title}) => {
  const translate = useTranslate();
  const router = useRouter();
  const [isMenuOpen, openMenu, closeMenu] = useBooleanState(false);

  const handleFollowSubEntry = (subEntry: SubNavigationEntry) => {
    closeMenu();
    router.redirect(router.generate(subEntry.route, subEntry.routeParams));
  };

  return (
    <Dropdown>
      <IconButton
        level="tertiary"
        title={title ? translate(title) : ''}
        icon={<MoreVerticalIcon />}
        ghost="borderless"
        onClick={openMenu}
        className="dropdown-button"
        data-testid={'openSubNavigationDropdownButton'}
      />
      {isMenuOpen && (
        <Dropdown.Overlay onClose={closeMenu}>
          {title && (
            <Dropdown.Header>
              <Dropdown.Title>{translate(title)}</Dropdown.Title>
            </Dropdown.Header>
          )}
          <Dropdown.ItemCollection>
            {entries.map(subEntry => (
              <Dropdown.Item onClick={() => handleFollowSubEntry(subEntry)} key={subEntry.code}>
                {subEntry.title}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {SubNavigationDropdown};
