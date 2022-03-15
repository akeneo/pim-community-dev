import {getColor, getFontSize, MailIcon, UserIcon} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../../../shared/translate';

const List = styled.ul`
    padding: 20px 0;
    font-size: ${getFontSize('bigger')};
`;

const Item = styled.li`
    display: flex;
    align-items: center;
    gap: 1ch;
    color: ${getColor('grey', 140)};
    margin-bottom: 10px;
`;

const StyledUserIcon = styled(UserIcon)`
    color: ${getColor('grey', 100)};
    width: 27px;
    height: 27px;
`;

const StyledMailIcon = styled(MailIcon)`
    color: ${getColor('grey', 100)};
    width: 27px;
    height: 27px;
`;

type Props = {
    scopes: Array<'email' | 'profile'>;
};

export const ConsentList = ({scopes}: Props) => {
    const translate = useTranslate();
    const firstname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.firstname');
    const lastname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.lastname');
    const email = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.email');
    return (
        <List>
            {scopes.includes('profile') && (
                <Item>
                    <StyledUserIcon />
                    <span
                        dangerouslySetInnerHTML={{
                            __html: translate(
                                'akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile',
                                {
                                    firstname: `
                                        <span class="AknConnectivityConnection-helper--highlight">
                                            ${firstname}
                                        </span>`,
                                    lastname: `
                                        <span class="AknConnectivityConnection-helper--highlight">
                                            ${lastname}
                                        </span>`,
                                }
                            ),
                        }}
                    />
                </Item>
            )}
            {scopes.includes('email') && (
                <Item>
                    <StyledMailIcon />
                    <span
                        dangerouslySetInnerHTML={{
                            __html: translate(
                                'akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email',
                                {
                                    email: `<span class="AknConnectivityConnection-helper--highlight">${email}</span>`,
                                }
                            ),
                        }}
                    />
                </Item>
            )}
        </List>
    );
};
