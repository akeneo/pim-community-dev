import {useTranslate} from '@akeneo-pim-community/shared';
import {BooleanInput, Field, Helper, MultiSelectInput} from 'akeneo-design-system';
import {useCallback, useEffect, useState} from 'react';
import styled from 'styled-components';
import {UserGroup, useFetchUserGroups} from '../hooks/useFetchUserGroups';
import {EnrichCategory} from '../models';
import {CategoryPermission, CategoryPermissions} from '../models/CategoryPermission';

type Props = {
  category: EnrichCategory;
  applyPermissionsOnChildren: boolean;
  onChangePermissions: (userGroups: UserGroup[], type: keyof CategoryPermissions, values: number[]) => void;
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

const EditPermissionsForm = ({
  category,
  applyPermissionsOnChildren,
  onChangePermissions,
  onChangeApplyPermissionsOnChildren,
}: Props) => {
  const translate = useTranslate();
  const {data: fetchedUserGroups, status: userGroupStatus} = useFetchUserGroups();
  const [userGroups, setUserGroup] = useState<UserGroup[] | null>(null);

  useEffect(() => {
    if (userGroupStatus === 'success') {
      if (fetchedUserGroups) {
        setUserGroup(fetchedUserGroups);
      }
    }
  }, [fetchedUserGroups, userGroupStatus]);

  const handleChangePermissions = useCallback(
    (type: keyof CategoryPermissions) => (values: string[]) => {
      if (userGroups) {
        onChangePermissions(
          userGroups,
          type,
          values.map(value => parseInt(value, 10))
        );
      }
    },
    [onChangePermissions, userGroups]
  );

  const makeGroupOptions = useCallback(
    (type: string) => {
      if (userGroups) {
        return userGroups?.map(({id, label}) => (
          <MultiSelectInput.Option value={id.toString()} key={`${type}-${id}`}>
            {label}
          </MultiSelectInput.Option>
        ));
      }
      return [];
    },
    [userGroups]
  );

  if (!category.permissions) {
    return <></>;
  }

  const groupOptions: {[permissionType: string]: JSX.Element[]} = {
    view: makeGroupOptions('view'),
    edit: makeGroupOptions('edit'),
    own: makeGroupOptions('own'),
  };

  const extractPermissionIdAsString = (permissions: CategoryPermission[]): string[] => {
    return permissions?.map(permission => permission.id?.toString());
  };

  return (
    <FormContainer>
      <PermissionField label={translate('category.permissions.view.label')}>
        <MultiSelectInput
          readOnly={false}
          value={extractPermissionIdAsString(category.permissions.view)}
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
          value={extractPermissionIdAsString(category.permissions.edit)}
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
          value={extractPermissionIdAsString(category.permissions.own)}
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
