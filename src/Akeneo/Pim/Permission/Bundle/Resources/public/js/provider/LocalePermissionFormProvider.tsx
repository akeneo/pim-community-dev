import React, {useEffect, useReducer, useState} from 'react';
import styled from 'styled-components';
import {
  getColor,
  Helper,
  Locale,
  EditIcon,
  Checkbox,
  EraseIcon,
  IconButton,
  MultiSelectInput,
  ViewIcon,
  SectionTitle,
} from 'akeneo-design-system';
import {
  PermissionFormProvider,
  PermissionFormReducer,
  PermissionSectionSummary,
  LevelSummaryField,
} from '@akeneo-pim-community/permission-form';
import {Actions} from '@akeneo-pim-community/permission-form/src/reducer/PermissionFormReducer';

const FetcherRegistry = require('pim/fetcher-registry');
const translate = require('oro/translator');
const securityContext = require('pim/security-context');

const Field = styled.div`
  display: flex;
  align-items: center;
  gap: 10px;
`;
const StyledLocale = styled(Locale)`
  margin-right: 15px;
`;

const PermissionTitle = styled.h3`
  color: ${getColor('grey', 140)};
  text-transform: uppercase;
  font-size: 15px;
  font-weight: 400;
`;

const Label = styled.label`
  color: ${getColor('grey', 120)};
  margin: 8px 0 6px 0;
  display: block;
`;

type LocaleType = {
  code: string;
  label: string;
  language: string;
};

const LocalePermissionFormProvider: PermissionFormProvider<PermissionFormReducer.State> = {
  key: 'locales',
  label: translate('pim_permissions.widget.entity.locale.label'),
  renderForm: (onChange, initialState: PermissionFormReducer.State | undefined) => {
    const [state, dispatch] = useReducer(
      PermissionFormReducer.reducer,
      initialState ?? PermissionFormReducer.initialState
    );
    const [activatedLocales, setActivatedLocales] = useState<LocaleType[]>([]);

    useEffect(() => {
      onChange(state);
    }, [state]);
    useEffect(() => {
      FetcherRegistry.getFetcher('locale')
        .fetchActivated({filter_locales: false})
        .then(setActivatedLocales);
    }, [setActivatedLocales]);

    const handleDispatchAdd = (
      localesFromState: string[],
      localesFromInput: string[],
      type: Actions.ADD_TO_VIEW | Actions.ADD_TO_EDIT
    ) => {
      const localeToAdd = localesFromInput.filter(locales => !localesFromState.includes(locales))[0];
      dispatch({type: type, identifier: localeToAdd});
    };
    const handleDispatchRemove = (
      localesFromState: string[],
      localesFromInput: string[],
      type: Actions.REMOVE_FROM_VIEW | Actions.REMOVE_FROM_EDIT
    ) => {
      const localeToRemove = localesFromState.filter(locales => !localesFromInput.includes(locales))[0];
      dispatch({type: type, identifier: localeToRemove});
    };

    const handleEditChange = (localesFromInput: string[]) => {
      if (state.edit.identifiers.length < localesFromInput.length) {
        handleDispatchAdd(state.edit.identifiers, localesFromInput, Actions.ADD_TO_EDIT);
      }
      if (state.edit.identifiers.length > localesFromInput.length) {
        handleDispatchRemove(state.edit.identifiers, localesFromInput, Actions.REMOVE_FROM_EDIT);
      }
    };
    const handleViewChange = (localesFromInput: string[]) => {
      if (state.view.identifiers.length < localesFromInput.length) {
        handleDispatchAdd(state.view.identifiers, localesFromInput, Actions.ADD_TO_VIEW);
      }
      if (state.view.identifiers.length > localesFromInput.length) {
        handleDispatchRemove(state.view.identifiers, localesFromInput, Actions.REMOVE_FROM_VIEW);
      }
    };

    return (
      <>
        <SectionTitle>
          <PermissionTitle>{translate('pim_permissions.widget.entity.locale.label')}</PermissionTitle>
        </SectionTitle>
        {securityContext.isGranted('pimee_enrich_locale_edit_permissions') ? (
          <Helper level="info">{translate('pim_permissions.widget.entity.locale.help')}</Helper>
        ) : (
          <Helper level="warning">
            {translate('pim_permissions.widget.entity.not_granted_warning', {
              permission: translate('pimee_enrich.acl.locale.edit_permissions'),
            })}
          </Helper>
        )}

        <Label>{translate('pim_permissions.widget.level.edit')}</Label>
        <Field>
          <MultiSelectInput
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            onChange={handleEditChange}
            removeLabel=""
            value={state.edit.identifiers}
            readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions') || state.edit.all}
          >
            {activatedLocales.map((locale: LocaleType) => (
              <MultiSelectInput.Option key={locale.code} value={locale.code}>
                {locale.label}
              </MultiSelectInput.Option>
            ))}
          </MultiSelectInput>
          <Checkbox
            checked={state.edit.all}
            readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions')}
            onChange={checked =>
              checked ? dispatch({type: Actions.ENABLE_ALL_EDIT}) : dispatch({type: Actions.DISABLE_ALL_EDIT})
            }
          >
            {translate('pim_permissions.widget.action.all')}
          </Checkbox>
          <IconButton
            ghost="borderless"
            level="tertiary"
            icon={<EraseIcon />}
            onClick={() => dispatch({type: Actions.CLEAR_EDIT})}
            title={translate('pim_permissions.widget.action.clear')}
          />
        </Field>

        <Label>{translate('pim_permissions.widget.level.view')}</Label>
        <Field>
          <MultiSelectInput
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            onChange={handleViewChange}
            removeLabel=""
            value={state.view.identifiers}
            readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions') || state.view.all}
          >
            {activatedLocales.map((locale: LocaleType) => (
              <MultiSelectInput.Option key={locale.code} value={locale.code}>
                {locale.label}
              </MultiSelectInput.Option>
            ))}
          </MultiSelectInput>
          <Checkbox
            checked={state.view.all}
            readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions')}
            onChange={checked =>
              checked ? dispatch({type: Actions.ENABLE_ALL_VIEW}) : dispatch({type: Actions.DISABLE_ALL_VIEW})
            }
          >
            {translate('pim_permissions.widget.action.all')}
          </Checkbox>
          <IconButton
            ghost="borderless"
            level="tertiary"
            icon={<EraseIcon />}
            onClick={() => dispatch({type: Actions.CLEAR_VIEW})}
            title={translate('pim_permissions.widget.action.clear')}
          />
        </Field>
      </>
    );
  },
  renderSummary: (state: PermissionFormReducer.State) => {
    const [activatedLocales, setActivatedLocales] = useState<LocaleType[]>([]);

    useEffect(() => {
      FetcherRegistry.getFetcher('locale')
        .fetchActivated({filter_locales: false})
        .then(setActivatedLocales);
    }, [setActivatedLocales]);

    const getLocales = (localesFromState: string[]) =>
      localesFromState.map((localeCode: string, key: number) => {
        const label = activatedLocales.find((locale: LocaleType) => locale.code === localeCode)?.label;

        return <StyledLocale key={key} code={localeCode} languageLabel={label ?? '[' + localeCode + ']'} />;
      });

    return (
      <PermissionSectionSummary label={'pim_permissions.widget.entity.locale.label'}>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.edit'} icon={<EditIcon size={20} />}>
          {state.edit.all ? translate('pim_permissions.widget.all') : getLocales(state.edit.identifiers)}
        </LevelSummaryField>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.view'} icon={<ViewIcon size={20} />}>
          {state.view.all ? translate('pim_permissions.widget.all') : getLocales(state.view.identifiers)}
        </LevelSummaryField>
      </PermissionSectionSummary>
    );
  },
  save: (_userGroup: string, _state: PermissionFormReducer.State) => {
    // @todo
    return Promise.resolve();
  },
  loadPermissions: async (userGroupName: string) => {
    const url = routing.generate('pimee_permissions_entities_get_user_group_locales', {
      userGroupName: userGroupName,
    });
    const response = await fetch(url, {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });

    if (false === response.ok) {
      return Promise.reject(`${response.status} ${response.statusText}`);
    }

    return response.json();
  },
};

export default LocalePermissionFormProvider;
