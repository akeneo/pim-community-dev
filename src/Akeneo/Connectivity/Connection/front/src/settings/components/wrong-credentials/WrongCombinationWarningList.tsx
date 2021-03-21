import React from 'react';
import {WrongCredentialsCombination} from '../../../model/wrong-credentials-combinations';
import {Translate, useTranslate} from '../../../shared/translate';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';
import {DocumentationLink} from './DocumentationLink';
import styled from 'styled-components';

const ListItem = styled.div`
    margin-top: 3px;
`;

type Props = {
    combinations: WrongCredentialsCombination;
    goodUsername: string;
};

export const WrongCombinationWarningList = ({combinations, goodUsername}: Props) => {
    const formatDate = useDateFormatter();
    const translate = useTranslate();

    return (
        <>
            <span
                dangerouslySetInnerHTML={{
                    __html: translate(
                        'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.several',
                        {
                            good_username: `
                        <span class='AknConnectivityConnection-helper--highlight'>
                            ${goodUsername}
                        </span>`,
                        }
                    ),
                }}
            />
            &nbsp;
            <DocumentationLink />
            <div>
                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.list' />
            </div>
            {Object.values(combinations.users).map(lastLogin => {
                return (
                    <ListItem key={lastLogin.username}>
                        <span
                            dangerouslySetInnerHTML={{
                                __html: translate(
                                    'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.username_date',
                                    {
                                        wrong_username: `
                                    <span class='AknConnectivityConnection-helper--highlight'>
                                        ${lastLogin.username}
                                    </span>`,
                                        date: formatDate(new Date(lastLogin.date), {month: 'short', day: 'numeric'}),
                                        time: formatDate(new Date(lastLogin.date), {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                        }),
                                    }
                                ),
                            }}
                        />
                    </ListItem>
                );
            })}
        </>
    );
};
