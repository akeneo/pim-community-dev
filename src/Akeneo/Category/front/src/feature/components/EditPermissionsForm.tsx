import React, {useCallback, useState} from 'react';
import {EditCategoryForm} from '../hooks';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BooleanInput, Field, Helper, MultiSelectInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {EnrichCategory} from '../models';

type Props = {
  formData: EnrichCategory | null;
  onChangePermissions: (type: string, values: string[]) => void;
  onChangeApplyPermissionsOnChildren: (value: boolean) => void;
};

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const PermissionField = styled(Field)`
  max-width: 400px;
`;

const EditPermissionsForm = ({formData, onChangePermissions, onChangeApplyPermissionsOnChildren}: Props) => {
  const translate = useTranslate();
  const [userGroupList] = useState([
    {
      id: 1,
      label: 'IT support'
    },
    {
      id: 2,
      label: 'Manager'
    },
    {
      id: 3,
      label: 'Furniture manager'
    },
  ]);

  const getUserGroupList = useCallback((permissionsId: number[]) => {
    const filteredUserGroupList = userGroupList.filter((userGroup) => {
        return permissionsId.includes(userGroup.id);
    });

    return filteredUserGroupList.map((userGroup) => userGroup.label);
  }, [userGroupList]);

  if (formData === null || !formData.permissions) {
    return <></>;
  }

  return (
    <FormContainer>
      <PermissionField label={translate('category.permissions.view.label')}>
        <MultiSelectInput
          readOnly={false}
          value={getUserGroupList(formData.permissions.view)}
          name='view-permission'
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('view', changedValues)}
        >
          {userGroupList.map(({id, label}) => (
            <MultiSelectInput.Option value={label} key={`view-${id}`}>
              {label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.edit.label')}>
        <MultiSelectInput
          value={getUserGroupList(formData.permissions.edit)}
          name='edit-permission'
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('edit', changedValues)}
        >
          {userGroupList.map(({id, label}) => (
            <MultiSelectInput.Option value={label} key={`edit-${id}`}>
              {label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.own.label')}>
        <MultiSelectInput
          value={getUserGroupList(formData.permissions.own)}
          name='own-permission'
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('own', changedValues)}
        >
          {userGroupList.map(({id, label}) => (
            <MultiSelectInput.Option value={label} key={`own-${id}`}>
              {label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.own.help')}</Helper>
      </PermissionField>
      <Field label={translate('category.permissions.apply_on_children.label')}>
        <BooleanInput
          clearable={false}
          readOnly={false}
          value={formData.permissions.apply_on_children === '1'}
          noLabel={translate('pim_common.no')}
          yesLabel={translate('pim_common.yes')}
          onChange={changedValue => onChangeApplyPermissionsOnChildren(changedValue)}
        />
        <Helper level="info">{translate('category.permissions.apply_on_children.help')}</Helper>
      </Field>
    </FormContainer>
  );
};

export {EditPermissionsForm};
