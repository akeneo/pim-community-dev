import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BooleanInput, Field, Helper, MultiSelectInput} from 'akeneo-design-system';
import {EditCategoryForm} from '../models';
import styled from 'styled-components';

type Props = {
  formData: EditCategoryForm | null;
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

  if (formData === null || !formData.permissions) {
    return <></>;
  }

  return (
    <FormContainer>
      <PermissionField label={translate('category.permissions.view.label')}>
        <MultiSelectInput
          readOnly={false}
          value={formData.permissions.view.value}
          name={formData.permissions.view.fullName}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('view', changedValues)}
        >
          {Object.entries(formData.permissions.view.choices).map(([key, choice]) => (
            <MultiSelectInput.Option value={choice.value} key={`view-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.edit.label')}>
        <MultiSelectInput
          value={formData.permissions.edit.value}
          name={formData.permissions.edit.fullName}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('edit', changedValues)}
        >
          {Object.entries(formData.permissions.edit.choices).map(([key, choice]) => (
            <MultiSelectInput.Option value={choice.value} key={`edit-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.own.label')}>
        <MultiSelectInput
          value={formData.permissions.own.value}
          name={formData.permissions.own.fullName}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={changedValues => onChangePermissions('own', changedValues)}
        >
          {Object.entries(formData.permissions.own.choices).map(([key, choice]) => (
            <MultiSelectInput.Option value={choice.value} key={`own-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.own.help')}</Helper>
      </PermissionField>
      <Field label={translate('category.permissions.apply_on_children.label')}>
        <BooleanInput
          clearable={false}
          readOnly={false}
          value={formData.permissions.apply_on_children.value === '1'}
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
