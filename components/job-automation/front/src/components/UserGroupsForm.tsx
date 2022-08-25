import React from 'react';
import {Field, SelectInput, Helper, MultiSelectInput} from 'akeneo-design-system';
import {useSecurity, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {filterDefaultUserGroup} from '../models';
import {useUserGroups} from '../hooks';

type UserGroupsFormProps = {
  userGroups: string[];
  validationErrors: ValidationError[];
  onUserGroupsChange: (userGroups: string[]) => void;
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

  return (
    <Field label={label}>
      <MultiSelectInput
        value={userGroups}
        onChange={onUserGroupsChange}
        onNextPage={loadNextPage}
        onSearchChange={searchName}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        removeLabel={translate('pim_common.remove')}
        readOnly={!isGranted('pim_user_group_index')}
        invalid={0 < validationErrors.length}
      >
        {filterDefaultUserGroup(availableUserGroups).map((userGroupLabel: string) => (
          <SelectInput.Option value={userGroupLabel} key={userGroupLabel}>
            {userGroupLabel}
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
