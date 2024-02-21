import React, {cloneElement, FC, isValidElement} from 'react';
import {Dropdown, IconButton, MoreIcon, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '../hooks';

const SecondaryActions: FC & {Item: typeof Dropdown.Item} = ({children}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  const items = React.Children.map(
    children,
    child =>
      isValidElement<{onClick?: () => void}>(child) &&
      cloneElement(child, {
        onClick: () => {
          close();
          child.props.onClick?.();
        },
      })
  );

  return (
    <Dropdown>
      <IconButton
        icon={<MoreIcon />}
        ghost="borderless"
        level="tertiary"
        title={translate('pim_common.other_actions')}
        onClick={open}
      />
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>{items}</Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

SecondaryActions.Item = Dropdown.Item;

export {SecondaryActions};
