import React from 'react';
import {Translate} from '../../shared/translate';

export const AuditableHelper = () => {
    return (
        <>
            <Translate id='akeneo_connectivity.connection.auditable_helper.message' />
            &nbsp;
            <a
                href='https://help.akeneo.com/pim/articles/manage-your-connections.html#enable-the-tracking'
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.auditable_helper.link' />
            </a>
        </>
    );
};
