import React, {FC} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize, MailIcon, UserIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../../../shared/translate';

const List = styled.ul<{viewMode: ViewMode} & AkeneoThemedProps>`
    padding: ${({viewMode}) => (viewMode === 'settings' ? '0' : '20px 0')};
    font-size: ${({viewMode}) => getFontSize(viewMode === 'settings' ? 'default' : 'bigger')};
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
    width: ${({$viewMode}) => ($viewMode === 'settings' ? '24px' : '27px')};
    height: ${({$viewMode}) => ($viewMode === 'settings' ? '24px' : '27px')};
`;

const StyledUserIcon = styled(UserIcon)`
    ${baseIconStyle}
`;
const StyledMailIcon = styled(MailIcon)`
    ${baseIconStyle}
`;

type ViewMode = 'wizard' | 'settings';

type Props = {
    scopes: Array<'email' | 'profile'>;
    viewMode?: ViewMode;
};

export const ConsentList: FC<Props> = ({scopes, viewMode = 'wizard'}) => {
    const translate = useTranslate();
    const firstname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.firstname');
    const lastname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.lastname');
    const email = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.email');
    return (
        <List viewMode={viewMode}>
            {scopes.includes('profile') && (
                <Item>
                    <StyledUserIcon $viewMode={viewMode} />
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
                    <StyledMailIcon $viewMode={viewMode} />
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
