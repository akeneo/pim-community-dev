import React, {useContext, useMemo} from 'react';
import {FlowType} from '../../../domain/apps/flow-type.enum';
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
                {id: FlowType.DATA_SOURCE, text: translate('pim_apps.flow_type.data_source', undefined, 1)},
                {id: FlowType.DATA_DESTINATION, text: translate('pim_apps.flow_type.data_destination', undefined, 1)},
                {id: FlowType.OTHER, text: translate('pim_apps.flow_type.other', undefined, 1)},
            ],
        }),
        [translate]
    );

    return <Select2 configuration={configuration} value={value} onChange={onChange as (flowType?: string) => void} />;
};
