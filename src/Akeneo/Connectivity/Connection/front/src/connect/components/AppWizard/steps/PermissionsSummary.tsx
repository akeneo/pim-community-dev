import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/connectivity-connection/src/shared/translate';
import {PermissionFormProvider} from '../../../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../../../model/Apps/permissions-by-provider-key';

const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
    height: 570px;
    overflow-y: scroll;
`;

const Connect = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

const AppTitle = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('title')};
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

const Helper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 17px 0 19px 0;
    width: 280px;
`;

type PermissionsSummarySectionProps = {
    provider: PermissionFormProvider<any>;
    permissions: PermissionsByProviderKey;
    onlyDisplayViewPermissions: boolean;
};

const PermissionsSummarySection: FC<PermissionsSummarySectionProps> = React.memo(
    ({provider, permissions, onlyDisplayViewPermissions}) => (
        <div>{provider.renderSummary(permissions, onlyDisplayViewPermissions)}</div>
    )
);

type Props = {
    appName: string;
    providers: PermissionFormProvider<any>[];
    permissions: PermissionsByProviderKey;
    onlyDisplayViewPermissions: boolean;
};
export const PermissionsSummary: FC<Props> = ({appName, providers, permissions, onlyDisplayViewPermissions}) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <AppTitle>{appName}</AppTitle>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.apps.wizard.permission.helper')}</p>
                <Link
                    href={
                        'https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#give-permissions-to-your-app-ee'
                    }
                >
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.permission.helper_link')}
                </Link>
            </Helper>
            {providers.map(provider => {
                const providerPermissions = false === permissions[provider.key] ? undefined : permissions[provider.key];

                return (
                    <PermissionsSummarySection
                        key={provider.key}
                        provider={provider}
                        permissions={providerPermissions}
                        onlyDisplayViewPermissions={onlyDisplayViewPermissions}
                    />
                );
            })}
        </InfoContainer>
    );
};
