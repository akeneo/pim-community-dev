import {getColor, getFontSize, Link} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../../shared/translate';

const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 0 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
`;

const Connect = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0;
`;

const AppTitle = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 40px;
    margin: 0;
`;

const Helper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 10px 0 20px 0;
    width: 280px;
`;

type Props = {
    appName: string;
    scopes: Array<'openid' | 'email' | 'profile'>;
};

export const Authentication = ({appName, scopes}: Props) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <AppTitle>
                {translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.title', {
                    app_name: appName,
                })}
            </AppTitle>
            <Helper>
                <p>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.helper')}{' '}
                    <Link href={'https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html'}>
                        {translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.helper_link')}
                    </Link>
                </p>
            </Helper>
            <div>
                {scopes.includes('profile') &&
                    translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')}
            </div>
            <div>
                {scopes.includes('email') &&
                    translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')}
            </div>
        </InfoContainer>
    );
};
