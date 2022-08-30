import React from 'react';
import {Field, SelectInput, Helper, MultiSelectInput, useDebounce} from 'akeneo-design-system';
import {useSecurity, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {removeDefaultUserGroup} from '../models';
import {useUserGroups} from '../hooks';
import {UserGroup} from '../models/UserGroup';

type UserGroupsFormProps = {
  userGroups: number[];
  validationErrors: ValidationError[];
  onUserGroupsChange: (userGroups: number[]) => void;
  label: string;
  disabledHelperMessage: string;
};

const UserGroupsForm = ({
  userGroups,
  validationErrors,
  onUserGroupsChange,
  label,
  disabledHelperMessage,
}: UserGroupsFormProps) => {
  const translate = useTranslate();
  const {availableUserGroups, loadNextPage, searchName} = useUserGroups();
  const {isGranted} = useSecurity();
  const debouncedLoadNextPage = useDebounce(loadNextPage);

  const handleUserGroupsChange = (userGroups: string[]) => {
    onUserGroupsChange(userGroups.map(userGroup => parseInt(userGroup)));
  };

  return (
    <Field label={label}>
      <MultiSelectInput
        value={userGroups.map(userGroup => userGroup.toString())}
        onChange={handleUserGroupsChange}
        onNextPage={debouncedLoadNextPage}
        onSearchChange={searchName}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        removeLabel={translate('pim_common.remove')}
        readOnly={!isGranted('pim_user_group_index')}
        invalid={0 < validationErrors.length}
      >
        {removeDefaultUserGroup(availableUserGroups).map((userGroup: UserGroup) => (
          <SelectInput.Option value={userGroup.id.toString()} key={userGroup.id}>
            {userGroup.label}
          </SelectInput.Option>
        ))}
      </MultiSelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {!isGranted('pim_user_group_index') && <Helper level="info">{disabledHelperMessage}</Helper>}
    </Field>
  );
};

export {UserGroupsForm};
