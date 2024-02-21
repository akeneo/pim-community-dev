import React from 'react';
import {useTranslate} from '../../../shared/translate';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';
import {DocumentationLink} from './DocumentationLink';

type Props = {
    lastLogin: {
        username: string;
        date: string;
    };
    goodUsername: string;
};

export const SingleWrongCombinationWarning = ({lastLogin, goodUsername}: Props) => {
    const formatDate = useDateFormatter();
    const translate = useTranslate();

    return (
        <>
            <span
                dangerouslySetInnerHTML={{
                    __html: translate(
                        'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.single',
                        {
                            wrong_username: `
                        <span class='AknConnectivityConnection-helper--highlight'>
                            ${lastLogin.username}
                        </span>`,
                            date: formatDate(lastLogin.date, {month: 'short', day: 'numeric'}),
                            time: formatDate(lastLogin.date, {hour: '2-digit', minute: '2-digit', second: '2-digit'}),
                            good_username: `
                        <span class='AknConnectivityConnection-helper--highlight'>
                            ${goodUsername}
                        </span>`,
                        }
                    ),
                }}
            />
            <div>
                <DocumentationLink />
            </div>
        </>
    );
};
