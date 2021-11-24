import React from 'react';
import {Checkbox, Dropdown, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {useTranslate, Translate} from '@akeneo-pim-community/shared';
import {useJobExecutionUsers} from '../../hooks/useJobExecutionUsers';

const getUserFilterValueLabel = (translate: Translate, userFilterValue: string[]): string => {
  switch (userFilterValue.length) {
    case 0:
      return translate('akeneo_job_process_tracker.users.all');
    case 1:
      return userFilterValue[0];
    default:
      return translate('pim_common.selected', {itemsCount: userFilterValue.length}, userFilterValue.length);
  }
};

type UserFilterProps = {
  userFilterValue: string[];
  onUserFilterChange: (userFilterValue: string[]) => void;
};

const UserFilter = ({userFilterValue, onUserFilterChange}: UserFilterProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const users = useJobExecutionUsers();

  return (
    <Dropdown>
      <SwitcherButton onClick={open} label={translate('akeneo_job_process_tracker.users.label')}>
        {getUserFilterValueLabel(translate, userFilterValue)}
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo_job_process_tracker.users.label')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item>
              <Checkbox checked={0 === userFilterValue.length} onChange={() => onUserFilterChange([])} />
              {translate('akeneo_job_process_tracker.users.all')}
            </Dropdown.Item>
            {users &&
              users.map(user => (
                <Dropdown.Item key={user}>
                  <Checkbox
                    checked={userFilterValue.includes(user)}
                    onChange={checked => {
                      if (checked) {
                        onUserFilterChange([...userFilterValue, user]);
                      } else {
                        onUserFilterChange(userFilterValue.filter(userFilterValueType => userFilterValueType !== user));
                      }
                    }}
                  />
                  {user}
                </Dropdown.Item>
              ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {UserFilter};
