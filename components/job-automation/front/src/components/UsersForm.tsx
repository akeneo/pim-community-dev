import React from 'react';
import {Field, SelectInput, Helper, MultiSelectInput} from 'akeneo-design-system';
import {useSecurity, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {useUsers} from '../hooks';

type UsersFormProps = {
  users: string[];
  validationErrors: ValidationError[];
  onUsersChange: (users: string[]) => void;
};

const UsersForm = ({users, validationErrors, onUsersChange}: UsersFormProps) => {
  const translate = useTranslate();
  const availableUsers = useUsers();
  const {isGranted} = useSecurity();

  return (
    <Field label={translate('akeneo.job_automation.notification.users.label')}>
      <MultiSelectInput
        value={users}
        onChange={onUsersChange}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        removeLabel={translate('pim_common.remove')}
        readOnly={!isGranted('pim_user_user_index')}
        invalid={0 < validationErrors.length}
      >
        {availableUsers.map(availableUser => (
          <SelectInput.Option value={availableUser} key={availableUser}>
            {availableUser}
          </SelectInput.Option>
        ))}
      </MultiSelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {!isGranted('pim_user_user_index') && (
        <Helper level="info">{translate('akeneo.job_automation.notification.users.disabled_helper')}</Helper>
      )}
    </Field>
  );
};

export {UsersForm};
