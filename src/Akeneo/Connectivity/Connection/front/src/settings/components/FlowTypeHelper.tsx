import React, {useContext} from 'react';
import {Translate, TranslateContext} from '../../shared/translate';

export const FlowTypeHelper = () => {
    const translate = useContext(TranslateContext);

    return (
        <>
            <Translate id='akeneo_connectivity.connection.flow_type_helper.message' />
            &nbsp;
            <a
                href={translate('akeneo_connectivity.connection.flow_type_helper.link_url')}
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.flow_type_helper.link' />
            </a>
        </>
    );
};
