import React, {FC} from 'react';
import styled from 'styled-components';
import {Checkbox, EraseIcon, IconButton} from 'akeneo-design-system';
import {MultiSelectInputWithDynamicOptions} from './MultiSelectInputWithDynamicOptions';
import translate from '../dependencies/translate';

const Field = styled.div`
    display: flex;
    align-items: center;
    gap: 10px;
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
    allByDefaultIsSelected: boolean;
    onSelectAllByDefault: () => void;
    onDeselectAllByDefault: () => void;
    onClear: () => void;
    ajaxUrl: string;
    processAjaxResponse: (response: any) => {
        results: Option[];
        more: boolean;
    };
    fetchByIdentifiers: (identifiers: string[]) => Promise<Option[]>;
};

export const PermissionFormWidget: FC<Props> = ({
    selection,
    onAdd,
    onRemove,
    disabled,
    allByDefaultIsSelected,
    onSelectAllByDefault,
    onDeselectAllByDefault,
    onClear,
    ajaxUrl,
    processAjaxResponse,
    fetchByIdentifiers,
}: Props) => {
    return (
        <Field>
            <MultiSelectInputWithDynamicOptions
                value={selection}
                onAdd={onAdd}
                onRemove={onRemove}
                disabled={disabled}
                url={ajaxUrl}
                processResults={processAjaxResponse}
                fetchByIdentifiers={fetchByIdentifiers}
            />
            <Checkbox
                checked={allByDefaultIsSelected}
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
            />
        </Field>
    );
};
