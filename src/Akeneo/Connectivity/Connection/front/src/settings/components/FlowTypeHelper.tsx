import React from 'react';
import {Translate} from '../../shared/translate';

export const FlowTypeHelper = () => {
    return (
        <>
            <Translate id='akeneo_connectivity.connection.flow_type_helper.message' />
            &nbsp;
            <a
                href='https://help.akeneo.com/pim/articles/manage-your-connections.html#choose-your-flow-type'
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.flow_type_helper.link' />
            </a>
        </>
    );
};
