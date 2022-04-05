import React, {FC} from 'react';
import {useTranslate} from '../../../../shared/translate';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize, MailIcon, UserIcon} from 'akeneo-design-system';

const List = styled.ul<AkeneoThemedProps>`
    margin: 10px 20px;
    font-size: ${getFontSize('default')};
`;

const Item = styled.li`
    display: flex;
    align-items: center;
    gap: 1ch;
    color: ${getColor('grey', 140)};
    margin-bottom: 10px;
`;

const baseIconStyle = css`
    color: ${getColor('grey', 100)};
    width: 24px;
    height: 24px;
`;

const StyledUserIcon = styled(UserIcon)`
    ${baseIconStyle}
`;
const StyledMailIcon = styled(MailIcon)`
    ${baseIconStyle}
`;

type Props = {
    scopes: Array<'email' | 'profile'>;
};

export const AuthenticationScopes: FC<Props> = ({scopes}) => {
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
