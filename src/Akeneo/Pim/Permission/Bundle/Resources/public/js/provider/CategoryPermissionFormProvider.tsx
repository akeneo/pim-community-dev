import React, {useEffect, useReducer} from 'react';
import styled from 'styled-components';
import {getColor, Helper, SectionTitle} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {PermissionFormProvider, PermissionFormWidget, PermissionFormReducer} from '@akeneo-pim-community/permission-form';

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

const categoriesAjaxUrl = routing.generate('pim_enrich_category_rest_list');

const processCategories = (data: any) => ({
    results: data.map((category: any) => ({
        id: category.code,
        text: getLabel(category.labels, UserContext.get('uiLocale'), `[${category.code}]`),
    })),
    more: false,
});

const fetchCategoriesByIdentifiers = (identifiers: string[]) => {
    return FetcherRegistry
        .getFetcher('category')
        .fetchByIdentifiers(identifiers)
        .then((results: any) => processCategories(results).results);
}

const CategoryPermissionFormProvider: PermissionFormProvider<PermissionFormReducer.State> = {
    key: 'categories',
    renderForm: (onChange) => {
        const [state, dispatch] = useReducer(PermissionFormReducer.reducer, PermissionFormReducer.initialState);

        useEffect(() => {
            onChange(state);
        }, [state]);

        return (
            <>
                <SectionTitle>
                    <H3>{translate('pim_permissions.widget.entity.category.label')}</H3>
                </SectionTitle>
                <Helper level="info">
                    {translate('pim_permissions.widget.entity.category.help')}
                </Helper>
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
                />
            </>
        );
    },
    renderPreview: (_state: PermissionFormReducer.State) => null,
    save: (_role: string, _state: PermissionFormReducer.State) => {
        // @todo
        return true;
    },
}

export default CategoryPermissionFormProvider;
