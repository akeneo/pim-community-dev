import React, {useEffect, useReducer, useState} from 'react';
import styled from 'styled-components';
import {getColor, Helper, Locale, EditIcon, ViewIcon, SectionTitle} from 'akeneo-design-system';
import {
  PermissionFormProvider,
  PermissionFormReducer,
  PermissionSectionSummary,
  LevelSummaryField,
  PermissionFormWidget,
} from '@akeneo-pim-community/permission-form';

const FetcherRegistry = require('pim/fetcher-registry');
const translate = require('oro/translator');
const routing = require('routing');
const securityContext = require('pim/security-context');

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
  renderForm: (onPermissionsChange, initialState: PermissionFormReducer.State | undefined, readOnly: boolean | undefined) => {
    const [state, dispatch] = useReducer(
      PermissionFormReducer.reducer,
      initialState ?? PermissionFormReducer.initialState
    );
    const [activatedLocales, setActivatedLocales] = useState<LocaleType[]>([]);

    readOnly = readOnly ?? false;

    useEffect(() => {
      readOnly !== true && onPermissionsChange(state);
    }, [readOnly, state]);

    useEffect(() => {
      FetcherRegistry.getFetcher('locale')
        .fetchActivated({filter_locales: false})
        .then(setActivatedLocales);
    }, [setActivatedLocales]);

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
        <PermissionFormWidget
          selection={state.edit.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_EDIT, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_EDIT, identifier: code})}
          disabled={state.edit.all}
          readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions') || readOnly}
          allByDefaultIsSelected={state.edit.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_EDIT})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_EDIT})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_EDIT})}
          options={activatedLocales.map(locale => ({id: locale.code, text: locale.label}))}
        />

        <Label>{translate('pim_permissions.widget.level.view')}</Label>
        <PermissionFormWidget
          selection={state.view.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_VIEW, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_VIEW, identifier: code})}
          disabled={state.view.all}
          readOnly={!securityContext.isGranted('pimee_enrich_locale_edit_permissions') || readOnly}
          allByDefaultIsSelected={state.view.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_VIEW})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_VIEW})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_VIEW})}
          options={activatedLocales.map(locale => ({id: locale.code, text: locale.label}))}
        />
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
  save: async (userGroup: string, state: PermissionFormReducer.State) => {
    if (false === securityContext.isGranted('pimee_enrich_locale_edit_permissions')) {
      return Promise.resolve();
    }

    const url = routing.generate('pimee_permissions_entities_set_locales');
    const response = await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
      body: JSON.stringify({
        user_group: userGroup,
        permissions: {
          edit: state.edit,
          view: state.view,
        },
      }),
    });

    if (false === response.ok) {
      return Promise.reject(`${response.status} ${response.statusText}`);
    }

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
