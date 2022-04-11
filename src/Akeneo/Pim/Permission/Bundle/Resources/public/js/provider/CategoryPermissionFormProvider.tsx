import React, {useEffect, useReducer, useState} from 'react';
import styled from 'styled-components';
import {getColor, Helper, KeyIcon, EditIcon, ViewIcon, SectionTitle} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {
  PermissionFormProvider,
  PermissionFormWidget,
  QueryParamsBuilder,
  PermissionFormReducer,
  PermissionSectionSummary,
  LevelSummaryField,
} from '@akeneo-pim-community/permission-form';

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

const categoriesAjaxUrl = routing.generate('pimee_permissions_entities_get_categories');

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

const processCategories = (data: Response) => ({
  results: data.results.map(category => ({
    id: category.code,
    text: category.label || `[${category.code}]`,
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

const fetchCategoriesByIdentifiers = (identifiers: string[]): Promise<Option[]> => {
  return FetcherRegistry.getFetcher('category')
    .fetchByIdentifiers(identifiers)
    .then((results: any) =>
      results.map((category: any) => ({
        id: category.code,
        text: getLabel(category.labels, UserContext.get('uiLocale'), `[${category.code}]`),
      }))
    );
};

type CategoryPermissionState = {
  own: {
    all: boolean;
    identifiers: string[];
  };
  edit: {
    all: boolean;
    identifiers: string[];
  };
  view: {
    all: boolean;
    identifiers: string[];
  };
};

const CategoryPermissionReducer = (state: CategoryPermissionState, action: PermissionFormReducer.Action) =>
  PermissionFormReducer.reducer<CategoryPermissionState>(state, action);

const defaultState: CategoryPermissionState = {
  own: {
    all: false,
    identifiers: [],
  },
  edit: {
    all: false,
    identifiers: [],
  },
  view: {
    all: false,
    identifiers: [],
  },
};

type Level = keyof CategoryPermissionState;

type SummaryLabels = {
  [k in Level]: string;
};

const getLevelSummary = async (state: CategoryPermissionState, level: Level): Promise<string> => {
  if (state[level].all) {
    return translate('pim_permissions.widget.all');
  }

  const categories = await fetchCategoriesByIdentifiers(state[level].identifiers);

  return categories.map(category => category.text).join(', ');
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

const CategoryPermissionFormProvider: PermissionFormProvider<CategoryPermissionState> = {
  key: 'categories',
  label: translate('pim_permissions.widget.entity.category.label'),
  renderForm: (
    onPermissionsChange,
    initialState: CategoryPermissionState | undefined = defaultState,
    readOnly: boolean = false,
    onlyDisplayViewPermissions = false
  ) => {
    const [state, dispatch] = useReducer(CategoryPermissionReducer, initialState);

    useEffect(() => {
      readOnly !== true && onPermissionsChange(state);
    }, [readOnly, state]);

    return (
      <>
        <SectionTitle>
          <H3>{translate('pim_permissions.widget.entity.category.label')}</H3>
        </SectionTitle>
        {securityContext.isGranted('pimee_enrich_category_edit_permissions') ? (
          <Helper level="info">{translate('pim_permissions.widget.entity.category.help')}</Helper>
        ) : (
          <Helper level="warning">
            {translate('pim_permissions.widget.entity.not_granted_warning', {
              permission: translate('pimee_enrich.acl.category.edit_permissions'),
            })}
          </Helper>
        )}

        {!onlyDisplayViewPermissions &&
          <>
            <Label>{translate('pim_permissions.widget.level.own')}</Label>
            <PermissionFormWidget
              selection={state.own.identifiers}
              onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_OWN, identifier: code})}
              onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_OWN, identifier: code})}
              disabled={state.own.all}
              readOnly={!securityContext.isGranted('pimee_enrich_category_edit_permissions') || readOnly}
              allByDefaultIsSelected={state.own.all}
              onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_OWN})}
              onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_OWN})}
              onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_OWN})}
              ajax={{
                ajaxUrl: categoriesAjaxUrl,
                processAjaxResponse: processCategories,
                fetchByIdentifiers: fetchCategoriesByIdentifiers,
                buildQueryParams: buildQueryParams,
              }}
            />
            <Label>{translate('pim_permissions.widget.level.edit')}</Label>
            <PermissionFormWidget
              selection={state.edit.identifiers}
              onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_EDIT, identifier: code})}
              onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_EDIT, identifier: code})}
              disabled={state.edit.all}
              readOnly={!securityContext.isGranted('pimee_enrich_category_edit_permissions') || readOnly}
              allByDefaultIsSelected={state.edit.all}
              onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_EDIT})}
              onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_EDIT})}
              onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_EDIT})}
              ajax={{
                ajaxUrl: categoriesAjaxUrl,
                processAjaxResponse: processCategories,
                fetchByIdentifiers: fetchCategoriesByIdentifiers,
                buildQueryParams: buildQueryParams,
              }}
            />
          </>
        }

        <Label>{translate('pim_permissions.widget.level.view')}</Label>
        <PermissionFormWidget
          selection={state.view.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_VIEW, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_VIEW, identifier: code})}
          disabled={state.view.all}
          readOnly={!securityContext.isGranted('pimee_enrich_category_edit_permissions') || readOnly}
          allByDefaultIsSelected={state.view.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_VIEW})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_VIEW})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_VIEW})}
          ajax={{
            ajaxUrl: categoriesAjaxUrl,
            processAjaxResponse: processCategories,
            fetchByIdentifiers: fetchCategoriesByIdentifiers,
            buildQueryParams: buildQueryParams,
          }}
        />
      </>
    );
  },
  renderSummary: (state: CategoryPermissionState) => {
    const [summaries, setSummaries] = useState<SummaryLabels>({
      own: '',
      edit: '',
      view: '',
    });

    useEffect(() => {
      (async () => {
        setSummaries({
          own: await getLevelSummary(state, 'own'),
          edit: await getLevelSummary(state, 'edit'),
          view: await getLevelSummary(state, 'view'),
        });
      })();
    }, [state, setSummaries]);

    return (
      <PermissionSectionSummary label={'pim_permissions.widget.entity.category.label'}>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.own'} icon={<KeyIcon size={20} />}>
          {summaries.own}
        </LevelSummaryField>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.edit'} icon={<EditIcon size={20} />}>
          {summaries.edit}
        </LevelSummaryField>
        <LevelSummaryField levelLabel={'pim_permissions.widget.level.view'} icon={<ViewIcon size={20} />}>
          {summaries.view}
        </LevelSummaryField>
      </PermissionSectionSummary>
    );
  },
  save: async (userGroup: string, state: CategoryPermissionState) => {
    if (!securityContext.isGranted('pimee_enrich_category_edit_permissions')) {
      return Promise.resolve();
    }

    const url = routing.generate('pimee_permissions_entities_set_categories');
    const response = await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
      body: JSON.stringify({
        user_group: userGroup,
        permissions: state,
      }),
    });

    if (false === response.ok) {
      return Promise.reject(`${response.status} ${response.statusText}`);
    }

    return Promise.resolve();
  },
  loadPermissions: async (userGroupName: string) => {
    const url = routing.generate('pimee_permissions_entities_get_user_group_categories', {
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

export default CategoryPermissionFormProvider;
