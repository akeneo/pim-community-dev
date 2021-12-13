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
    scopes: Array<'openid' | 'email' | 'profile'>;
};

export const ConsentList = ({scopes}: Props) => {
    const translate = useTranslate();

    return (
        <List>
            {scopes.includes('profile') && (
                <Item>
                    <StyledUserIcon />
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')}
                </Item>
            )}
            {scopes.includes('email') && (
                <Item>
                    <StyledMailIcon />
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')}
                </Item>
            )}
        </List>
    );
};
