import {AkeneoThemedProps, Badge, getColor, getFontSize, MailIcon, UserIcon} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../../../shared/translate';

const List = styled.ul`
    padding: 20px 0;
    font-size: ${getFontSize('bigger')};
`;

const Item = styled.li.attrs((props: {highlightMode?: 'new' | 'old' | null} & AkeneoThemedProps) => ({
    highlightMode: props.highlightMode,
}))`
    display: flex;
    align-items: center;
    gap: 1ch;
    color: ${props => getColor('grey', props.highlightMode === 'old' ? 120 : 140)};
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

const NewBadge = styled(Badge)`
    margin-left: 10px;
`;

type Props = {
    scopes: Array<'email' | 'profile'>;
    highlightMode?: 'new' | 'old' | null;
};

export const ConsentList = ({scopes, highlightMode}: Props) => {
    const translate = useTranslate();
    const firstname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.firstname');
    const lastname = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.lastname');
    const email = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.email');
    return (
        <List>
            {scopes.includes('profile') && (
                <Item highlightMode={highlightMode}>
                    <StyledUserIcon />
                    <span
                        dangerouslySetInnerHTML={{
                            __html: translate(
                                'akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile',
                                {
                                    firstname: `
                                        <span class="AknConnectivityConnection-helper--highlight${
                                            'old' === highlightMode ? '--lighter' : ''
                                        }">
                                            ${firstname}
                                        </span>`,
                                    lastname: `
                                        <span class="AknConnectivityConnection-helper--highlight${
                                            'old' === highlightMode ? '--lighter' : ''
                                        }">
                                            ${lastname}
                                        </span>`,
                                }
                            ),
                        }}
                    />
                    {'new' === highlightMode && (
                        <NewBadge level={'secondary'}>
                            {translate('akeneo_connectivity.connection.connect.apps.scope.new')}
                        </NewBadge>
                    )}
                </Item>
            )}
            {scopes.includes('email') && (
                <Item highlightMode={highlightMode}>
                    <StyledMailIcon />
                    <span
                        dangerouslySetInnerHTML={{
                            __html: translate(
                                'akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email',
                                {
                                    email: `<span class="AknConnectivityConnection-helper--highlight${
                                        'old' === highlightMode ? '--lighter' : ''
                                    }">${email}</span>`,
                                }
                            ),
                        }}
                    />
                    {'new' === highlightMode && (
                        <NewBadge level={'secondary'}>
                            {translate('akeneo_connectivity.connection.connect.apps.scope.new')}
                        </NewBadge>
                    )}
                </Item>
            )}
        </List>
    );
};
