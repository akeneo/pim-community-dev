import React, {useContext} from 'react';
import {Translate, TranslateContext} from '../../shared/translate';

export const FlowTypeHelper = () => {
    const translate = useContext(TranslateContext);

    return (
        <>
            <Translate id='pim_apps.flow_type_helper.message' />
            &nbsp;
            <a href={translate('pim_apps.flow_type_helper.link_url')}>
                <Translate id='pim_apps.flow_type_helper.link' />
            </a>
        </>
    );
};
