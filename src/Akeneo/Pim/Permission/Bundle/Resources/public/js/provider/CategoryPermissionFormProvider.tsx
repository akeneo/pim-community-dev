import React, {useEffect, useReducer} from 'react';
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

const fetchCategoriesByIdentifiers = (identifiers: string[]) => {
  return FetcherRegistry.getFetcher('category')
    .fetchByIdentifiers(identifiers)
    .then((results: any) =>
      results.map((category: any) => ({
        id: category.code,
        text: getLabel(category.labels, UserContext.get('uiLocale'), `[${category.code}]`),
      }))
    );
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

const CategoryPermissionFormProvider: PermissionFormProvider<PermissionFormReducer.State> = {
  key: 'categories',
  renderForm: (onChange, initialState: PermissionFormReducer.State | undefined) => {
    const [state, dispatch] = useReducer(
      PermissionFormReducer.reducer,
      initialState ?? PermissionFormReducer.initialState
    );

    useEffect(() => {
      onChange(state);
    }, [state]);

    return (
      <>
        <SectionTitle>
          <H3>{translate('pim_permissions.widget.entity.category.label')}</H3>
        </SectionTitle>
        <Helper level="info">{translate('pim_permissions.widget.entity.category.help')}</Helper>
        <Label>{translate('pim_permissions.widget.level.own')}</Label>
        <PermissionFormWidget
          selection={state.own.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_OWN, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_OWN, identifier: code})}
          disabled={state.own.all}
          allByDefaultIsSelected={state.own.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_OWN})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_OWN})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_OWN})}
          ajaxUrl={categoriesAjaxUrl}
          processAjaxResponse={processCategories}
          fetchByIdentifiers={fetchCategoriesByIdentifiers}
          buildQueryParams={buildQueryParams}
        />
        <Label>{translate('pim_permissions.widget.level.edit')}</Label>
        <PermissionFormWidget
          selection={state.edit.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_EDIT, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_EDIT, identifier: code})}
          disabled={state.edit.all}
          allByDefaultIsSelected={state.edit.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_EDIT})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_EDIT})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_EDIT})}
          ajaxUrl={categoriesAjaxUrl}
          processAjaxResponse={processCategories}
          fetchByIdentifiers={fetchCategoriesByIdentifiers}
          buildQueryParams={buildQueryParams}
        />
        <Label>{translate('pim_permissions.widget.level.view')}</Label>
        <PermissionFormWidget
          selection={state.view.identifiers}
          onAdd={code => dispatch({type: PermissionFormReducer.Actions.ADD_TO_VIEW, identifier: code})}
          onRemove={code => dispatch({type: PermissionFormReducer.Actions.REMOVE_FROM_VIEW, identifier: code})}
          disabled={state.view.all}
          allByDefaultIsSelected={state.view.all}
          onSelectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.ENABLE_ALL_VIEW})}
          onDeselectAllByDefault={() => dispatch({type: PermissionFormReducer.Actions.DISABLE_ALL_VIEW})}
          onClear={() => dispatch({type: PermissionFormReducer.Actions.CLEAR_VIEW})}
          ajaxUrl={categoriesAjaxUrl}
          processAjaxResponse={processCategories}
          fetchByIdentifiers={fetchCategoriesByIdentifiers}
          buildQueryParams={buildQueryParams}
        />
      </>
    );
  },
  renderSummary: (state: PermissionFormReducer.State) => (
    <PermissionSectionSummary label={'pim_permissions.widget.entity.category.label'}>
      <LevelSummaryField levelLabel={'pim_permissions.widget.level.own'} icon={<KeyIcon size={20} />}>
        {state.own.all ? translate('pim_permissions.widget.all') : state.own.identifiers.join(', ')}
      </LevelSummaryField>
      <LevelSummaryField levelLabel={'pim_permissions.widget.level.edit'} icon={<EditIcon size={20} />}>
        {state.edit.all ? translate('pim_permissions.widget.all') : state.edit.identifiers.join(', ')}
      </LevelSummaryField>
      <LevelSummaryField levelLabel={'pim_permissions.widget.level.view'} icon={<ViewIcon size={20} />}>
        {state.view.all ? translate('pim_permissions.widget.all') : state.view.identifiers.join(', ')}
      </LevelSummaryField>
    </PermissionSectionSummary>
  ),
  save: (_role: string, _state: PermissionFormReducer.State) => {
    // @todo
    return true;
  },
};

export default CategoryPermissionFormProvider;
