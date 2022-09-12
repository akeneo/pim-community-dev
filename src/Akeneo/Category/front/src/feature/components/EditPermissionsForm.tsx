import React, {useCallback, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {BooleanInput, Field, Helper, MultiSelectInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {CategoryPermissions, EnrichCategory} from '../models';

type Props = {
  category: EnrichCategory;
  applyPermissionsOnChildren: boolean;
  onChangePermissions: (type: keyof CategoryPermissions, values: number[]) => void;
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

const asString = (n: number): string => n.toString();
const asNumber = (s: string): number => parseInt(s, 10);
const asStringArr = (a: number[]): string[] => a.map(asString);
const asNumberArr = (a: string[]): number[] => a.map(asNumber);

const EditPermissionsForm = ({
  category,
  applyPermissionsOnChildren,
  onChangePermissions,
  onChangeApplyPermissionsOnChildren,
}: Props) => {
  const translate = useTranslate();
  const [userGroupList] = useState([
    {
      id: 1,
      label: 'IT support',
    },
    {
      id: 2,
      label: 'Manager',
    },
    {
      id: 3,
      label: 'Furniture manager',
    },
    {
      id: 7,
      label: 'All',
    },
  ]);

  const handleChangePermissions = useCallback(
    (type: keyof CategoryPermissions) => (value: string[]) => onChangePermissions(type, asNumberArr(value)),
    [onChangePermissions]
  );

  const makeGroupOptions = useCallback(
    (type: string) =>
      userGroupList.map(({id, label}) => (
        <MultiSelectInput.Option value={id.toString()} key={`${type}-${id}`}>
          {label}
        </MultiSelectInput.Option>
      )),
    [userGroupList]
  );

  if (!category.permissions) {
    return <></>;
  }

  const groupOptions: {[permissionType: string]: JSX.Element[]} = {
    view: makeGroupOptions('view'),
    edit: makeGroupOptions('edit'),
    own: makeGroupOptions('own'),
  };

  const valuesAstring = {
    view: asStringArr(category.permissions.view),
    edit: asStringArr(category.permissions.edit),
    own: asStringArr(category.permissions.own),
  };

  return (
    <FormContainer>
      <PermissionField label={translate('category.permissions.view.label')}>
        <MultiSelectInput
          readOnly={false}
          value={valuesAstring['view']}
          name="view-permission"
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={handleChangePermissions('view')}
        >
          {groupOptions['view']}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.edit.label')}>
        <MultiSelectInput
          value={valuesAstring['edit']}
          name="edit-permission"
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={handleChangePermissions('edit')}
        >
          {groupOptions['edit']}
        </MultiSelectInput>
      </PermissionField>
      <PermissionField label={translate('category.permissions.own.label')}>
        <MultiSelectInput
          value={valuesAstring['own']}
          name="own-permission"
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          removeLabel={translate('pim_common.remove')}
          onChange={handleChangePermissions('own')}
        >
          {groupOptions['own']}
        </MultiSelectInput>
        <Helper level="info">{translate('category.permissions.own.help')}</Helper>
      </PermissionField>
      <Field label={translate('category.permissions.apply_on_children.label')}>
        <BooleanInput
          clearable={false}
          readOnly={false}
          value={applyPermissionsOnChildren}
          noLabel={translate('pim_common.no')}
          yesLabel={translate('pim_common.yes')}
          onChange={onChangeApplyPermissionsOnChildren}
        />
        <Helper level="info">{translate('category.permissions.apply_on_children.help')}</Helper>
      </Field>
    </FormContainer>
  );
};

export {EditPermissionsForm};
