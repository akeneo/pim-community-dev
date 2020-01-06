import React, {useContext, useMemo} from 'react';
import {FlowType} from '../../model/flow-type.enum';
import {Select2} from '../../common';
import {TranslateContext} from '../../shared/translate';

interface Props {
    value: FlowType;
    onChange: (flowType: FlowType) => void;
}

export const FlowTypeSelect = ({value, onChange}: Props) => {
    const translate = useContext(TranslateContext);

    const configuration = useMemo(
        () => ({
            minimumResultsForSearch: -1,
            data: [
                {
                    id: FlowType.DATA_SOURCE,
                    text: translate('akeneo_connectivity.connection.flow_type.data_source', undefined, 1),
                },
                {
                    id: FlowType.DATA_DESTINATION,
                    text: translate('akeneo_connectivity.connection.flow_type.data_destination', undefined, 1),
                },
                {id: FlowType.OTHER, text: translate('akeneo_connectivity.connection.flow_type.other', undefined, 1)},
            ],
        }),
        [translate]
    );

    return <Select2 configuration={configuration} value={value} onChange={onChange as (flowType?: string) => void} />;
};
