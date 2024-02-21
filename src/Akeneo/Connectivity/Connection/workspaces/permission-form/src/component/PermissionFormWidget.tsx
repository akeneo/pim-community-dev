import React, {FC} from 'react';
import styled from 'styled-components';
import {Checkbox, EraseIcon, IconButton} from 'akeneo-design-system';
import {MultiSelectInputWithDynamicOptions, QueryParamsBuilder} from './MultiSelectInputWithDynamicOptions';
import {MultiSelectInputWithStaticOptions} from './MultiSelectInputWithStaticOptions';
import translate from '../dependencies/translate';

const Field = styled.div`
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 460px;
`;

type Option = {
    id: string;
    text: string;
};

type Props = {
    selection: string[];
    onAdd: (identifier: string) => void;
    onRemove: (identifier: string) => void;
    disabled: boolean;
    readOnly: boolean;
    allByDefaultIsSelected: boolean;
    onSelectAllByDefault: () => void;
    onDeselectAllByDefault: () => void;
    onClear: () => void;
    ajax?: {
        ajaxUrl: string;
        fetchByIdentifiers: (identifiers: string[]) => Promise<Option[]>;
        processAjaxResponse: (response: any) => {
            results: Option[];
            more: boolean;
            context: any;
        };
        buildQueryParams?: QueryParamsBuilder<any, any>;
    };
    options?: Option[];
};

export const PermissionFormWidget: FC<Props> = ({
    selection,
    onAdd,
    onRemove,
    disabled,
    readOnly,
    allByDefaultIsSelected,
    onSelectAllByDefault,
    onDeselectAllByDefault,
    onClear,
    ajax,
    options,
}: Props) => {
    return (
        <Field>
            {undefined !== ajax && (
                <MultiSelectInputWithDynamicOptions
                    value={selection}
                    onAdd={onAdd}
                    onRemove={onRemove}
                    disabled={disabled || readOnly}
                    url={ajax.ajaxUrl}
                    processResults={ajax.processAjaxResponse}
                    fetchByIdentifiers={ajax.fetchByIdentifiers}
                    buildQueryParams={ajax.buildQueryParams}
                />
            )}
            {undefined !== options && (
                <MultiSelectInputWithStaticOptions
                    value={selection}
                    onAdd={onAdd}
                    onRemove={onRemove}
                    disabled={disabled || readOnly}
                    options={options}
                />
            )}
            <Checkbox
                checked={allByDefaultIsSelected}
                readOnly={readOnly}
                onChange={checked => {
                    checked ? onSelectAllByDefault() : onDeselectAllByDefault();
                }}
            >
                {translate('pim_permissions.widget.action.all')}
            </Checkbox>
            <IconButton
                ghost='borderless'
                level='tertiary'
                icon={<EraseIcon />}
                onClick={onClear}
                title={translate('pim_permissions.widget.action.clear')}
                disabled={readOnly}
            />
        </Field>
    );
};
