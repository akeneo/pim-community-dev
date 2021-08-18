import {SelectInput} from 'akeneo-design-system';
import React, {FC, useContext} from 'react';
import {FlowType} from '../../model/flow-type.enum';
import {TranslateContext} from '../../shared/translate';

interface Props {
    value: FlowType;
    onChange: (flowType: FlowType) => void;
    id: string;
}

export const FlowTypeSelect: FC<Props> = ({value, onChange, id}: Props) => {
    const translate = useContext(TranslateContext);

    return (
        <SelectInput
            value={value}
            onChange={value => onChange(value as FlowType)}
            clearable={false}
            emptyResultLabel=''
            openLabel=''
            id={id}
        >
            <SelectInput.Option value={FlowType.DATA_SOURCE}>
                {translate('akeneo_connectivity.connection.flow_type.data_source', undefined, 1)}
            </SelectInput.Option>
            <SelectInput.Option value={FlowType.DATA_DESTINATION}>
                {translate('akeneo_connectivity.connection.flow_type.data_destination', undefined, 1)}
            </SelectInput.Option>
            <SelectInput.Option value={FlowType.OTHER}>
                {translate('akeneo_connectivity.connection.flow_type.other', undefined, 1)}
            </SelectInput.Option>
        </SelectInput>
    );
};
