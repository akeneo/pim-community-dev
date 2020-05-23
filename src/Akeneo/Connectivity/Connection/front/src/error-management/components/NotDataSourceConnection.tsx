import React, {FC} from 'react';
import {EmptyState} from '../../common';
import {FlowType} from '../../model/flow-type.enum';
import {Translate, useTranslate} from '../../shared/translate';

const NotDataSourceConnection: FC<{flowType: FlowType}> = ({flowType}) => {
    const translate = useTranslate();

    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration />
            <EmptyState.Heading>
                <Translate
                    id='akeneo_connectivity.connection.error_management.connection_monitoring.not_data_source.title'
                    placeholders={{
                        flow_type: translate(`akeneo_connectivity.connection.flow_type.${flowType}`, undefined, 1),
                    }}
                />
            </EmptyState.Heading>
        </EmptyState.EmptyState>
    );
};

export {NotDataSourceConnection};
