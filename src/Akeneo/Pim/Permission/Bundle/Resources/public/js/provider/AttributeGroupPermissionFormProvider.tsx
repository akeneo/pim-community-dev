import React, {useEffect, useReducer, useState} from 'react';
import styled from 'styled-components';
import {getColor, Helper, EditIcon, ViewIcon, SectionTitle} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {
  PermissionFormProvider,
  PermissionFormWidget,
  QueryParamsBuilder,
  PermissionFormReducer,
  PermissionSectionSummary,
  LevelSummaryField,
} from '@akeneo-pim-community/permission-form';
import {useFeatureFlags} from '@akeneo-pim-community/shared';

const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const translate = require('oro/translator');
const routing = require('routing');
const securityContext = require('pim/security-context');

const H3 = styled.h3`
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

const attributeGroupsAjaxUrl = routing.generate('pimee_permissions_entities_get_attribute_groups');

type Response = {
  results: {
    code: string;
    label: string | null;
  }[];
  next: {
    url: string | null;
    params: PaginationParams;
  };
};

const processAttributeGroups = (data: Response) => ({
  results: data.results.map(attributeGroup => ({
    id: attributeGroup.code,
    text: attributeGroup.label || `[${attributeGroup.code}]`,
  })),
  more: data.next.url !== null,
  context: {
    next: data.next,
  },
});

type Option = {
  id: string;
  text: string;
};

const fetchAttributeGroupsByIdentifiers = (identifiers: string[]): Promise<Option[]> => {
  return FetcherRegistry.getFetcher('attribute-group')
    .fetchByIdentifiers(identifiers, {
      apply_filters: false,
    })
    .then((results: any) =>
      results.map((attributeGroup: any) => ({
        id: attributeGroup.code,
        text: getLabel(attributeGroup.labels, UserContext.get('uiLocale'), `[${attributeGroup.code}]`),
      }))
    );
};

type AttributeGroupPermissionState = {
  edit: {
    all: boolean;
    identifiers: string[];
  };
  view: {
    all: boolean;
    identifiers: string[];
  };
};

const AttributeGroupPermissionReducer = (state: AttributeGroupPermissionState, action: PermissionFormReducer.Action) =>
  PermissionFormReducer.reducer<AttributeGroupPermissionState>(state, action);

const defaultState: AttributeGroupPermissionState = {
  edit: {
    all: false,
    identifiers: [],
  },
  view: {
    all: false,
    identifiers: [],
  },
};

type Level = keyof AttributeGroupPermissionState;

type SummaryLabels = {
  [k in Level]: string;
};

const getLevelSummary = async (state: AttributeGroupPermissionState, level: Level): Promise<string> => {
  if (state[level].all) {
    return translate('pim_permissions.widget.all');
  }

  const attributeGroups = await fetchAttributeGroupsByIdentifiers(state[level].identifiers);

  return attributeGroups.map(attributeGroup => attributeGroup.text).join(', ');
};

type PaginationContext = {
  next: {
    url: string | null;
    params: PaginationParams;
  };
};

type PaginationParams = {
  search?: string;
  limit?: number;
  offset?: number;
};

const buildQueryParams: QueryParamsBuilder<PaginationContext, PaginationParams> = (
  search: string,
  _page: number,
  context: PaginationContext | null
) => {
  const params = {
    search: search,
  };

  if (null !== context) {
    return {
      ...context.next.params,
      ...params,
    };
  }

  return params;
};

const AttributeGroupPermissionFormProvider: PermissionFormProvider<AttributeGroupPermissionState> = {
  key: 'attribute-groups',
  label: translate('pim_permissions.widget.entity.attribute_group.label'),
  renderForm: (
    onPermissionsChange,
    initialState: AttributeGroupPermissionState | undefined = defaultState,
    readOnly: boolean = false,
    onlyDisplayViewPermissions = false
  ) => {
    const [state, dispatch] = useReducer(AttributeGroupPermissionReducer, initialState);
    const canEditPermissions =
      securityContext.isGranted('pimee_enrich_attribute_group_edit_permissions') &&
      useFeatureFlags().isEnabled('permission');

    useEffect(() => {
      readOnly !== true && onPermissionsChange(state);
    }, [readOnly, state]);

    return (
      <>
        <SectionTitle>
          <H3>{translate('pim_permissions.widget.entity.attribute_group.label')}</H3>
        </SectionTitle>
        {!onlyDisplayViewPermissions && (
          <>
            {canEditPermissions ? (
              <Helper level="info">{translate('pim_permissions.widget.entity.attribute_group.help')}</Helper>
            ) : (
              <Helper level="warning">
                {translate('pim_permissions.widget.entity.not_granted_warning', {
                  permission: translate('pimee_enrich.acl.attribute_group.edit_permissions'),
                })}
              </Helper>
            )}
            <Label>{translate('pim_permissions.widget.level.edit')}</Label>
            <PermissionFormWidget
              selection={state.edit.identifiers}
              onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_EDIT, identifier: code})}
              onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_EDIT, identifier: code})}
              disabled={state.edit.all}
              readOnly={!canEditPermissions || readOnly}
              allByDefaultIsSelected={state.edit.all}
              onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_EDIT})}
              onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_EDIT})}
              onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_EDIT})}
              ajax={{
                ajaxUrl: attributeGroupsAjaxUrl,
                processAjaxResponse: processAttributeGroups,
                fetchByIdentifiers: fetchAttributeGroupsByIdentifiers,
                buildQueryParams: buildQueryParams,
              }}
            />
          </>
        )}
        <Label>{translate('pim_permissions.widget.level.view')}</Label>
        <PermissionFormWidget
          selection={state.view.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_VIEW, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_VIEW, identifier: code})}
          disabled={state.view.all}
          readOnly={!canEditPermissions || readOnly}
          allByDefaultIsSelected={state.view.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_VIEW})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_VIEW})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_VIEW})}
          ajax={{
            ajaxUrl: attributeGroupsAjaxUrl,
            processAjaxResponse: processAttributeGroups,
            fetchByIdentifiers: fetchAttributeGroupsByIdentifiers,
            buildQueryParams: buildQueryParams,
          }}
        />
      </>
    );
  },
  renderSummary: (state: AttributeGroupPermissionState) => {
    const [summaries, setSummaries] = useState<SummaryLabels>({
      edit: '',
      view: '',
    });

    useEffect(() => {
      (async () => {
        setSummaries({
          edit: await getLevelSummary(state, 'edit'),
          view: await getLevelSummary(state, 'view'),
        });
      })();
    }, [state, setSummaries]);

    return (
      <PermissionSectionSummary label={'pim_permissions.widget.entity.attribute_group.label'}>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.edit'} icon={<EditIcon size={20} />}>
          {summaries.edit}
        </LevelSummaryField>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.view'} icon={<ViewIcon size={20} />}>
          {summaries.view}
        </LevelSummaryField>
      </PermissionSectionSummary>
    );
  },
  save: async (userGroup: string, state: AttributeGroupPermissionState) => {
    const canEditPermissions =
      securityContext.isGranted('pimee_enrich_attribute_group_edit_permissions') &&
      useFeatureFlags().isEnabled('permission');

    if (!canEditPermissions) {
      return Promise.resolve();
    }

    const url = routing.generate('pimee_permissions_entities_set_attribute_groups');
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
    const url = routing.generate('pimee_permissions_entities_get_user_group_attribute_groups', {
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

export default AttributeGroupPermissionFormProvider;
