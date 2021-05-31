import React from 'react';
import {EditCategoryForm} from "../../hooks";
import {useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";
import {BooleanInput, Field, Helper, MultiSelectInput} from "akeneo-design-system";

const FormContainer = styled.form`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

type Props = {
  formData: EditCategoryForm | null;
  onChangePermissions: (type: string, values: string[]) => void;
  onChangeApplyPermissionsOnChilren: (value: boolean) => void;
};

const EditPermissionsForm = ({formData, onChangePermissions, onChangeApplyPermissionsOnChilren}: Props) => {
  const translate = useTranslate();

  if (formData === null || !formData.permissions) {
    return (<></>);
  }

  return (
    <FormContainer>
      <Field label={translate('category.permissions.view.label')}>
        <MultiSelectInput
          readOnly={false}
          value={formData.permissions.view.value}
          name={formData.permissions.view.fullName}
          removeLabel="Remove"
          emptyResultLabel="Empty result"
          onChange={changedValues => onChangePermissions('view', changedValues)}
        >
          {Object.entries(formData.permissions.view.choices).map(([key, choice]) =>
            <MultiSelectInput.Option value={choice.value} key={`view-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          )}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.view.help')}</Helper>
      </Field>
      <Field label={translate('category.permissions.edit.label')}>
        <MultiSelectInput
          value={formData.permissions.edit.value}
          name={formData.permissions.edit.fullName}
          removeLabel="Remove"
          emptyResultLabel="Empty result"
          onChange={changedValues => onChangePermissions('edit', changedValues)}
        >
          {Object.entries(formData.permissions.edit.choices).map(([key, choice]) =>
            <MultiSelectInput.Option value={choice.value} key={`edit-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          )}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.edit.help')}</Helper>
      </Field>
      <Field label={translate('category.permissions.own.label')}>
        <MultiSelectInput
          value={formData.permissions.own.value}
          name={formData.permissions.own.fullName}
          removeLabel="Remove"
          emptyResultLabel="Empty result"
          onChange={changedValues => onChangePermissions('own', changedValues)}
        >
          {Object.entries(formData.permissions.own.choices).map(([key, choice]) =>
            <MultiSelectInput.Option value={choice.value} key={`own-${key}`}>
              {choice.label}
            </MultiSelectInput.Option>
          )}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.own.help')}</Helper>
      </Field>
      <Field label={translate('category.permissions.apply_on_children.label')}>
        <BooleanInput
          clearable={false}
          readOnly={false}
          value={formData.permissions.apply_on_children.value === '1'}
          noLabel={translate('pim_common.no')}
          yesLabel={translate('pim_common.yes')}
          onChange={changedValue => onChangeApplyPermissionsOnChilren(changedValue)}
        />
        <Helper level="info">{translate('category.permissions.apply_on_children.help')}</Helper>
      </Field>
    </FormContainer>
  );
};

export {EditPermissionsForm};
