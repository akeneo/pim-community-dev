import React from 'react';
import {Translate} from '../../../shared/translate';
import {HelperLink} from '../../../common';

export const DocumentationLink = () => {
    return (
        <>
            <HelperLink
                href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#why-should-you-use-the-connection-username'
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.link' />
            </HelperLink>
        </>
    );
};
