import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/connectivity-connection/src/shared/translate';
import {PermissionFormProvider} from '../../../shared/permission-form-registry';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import {PermissionsForm} from '../PermissionsForm';

const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
    height: 545px;
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

type Props = {
    appName: string;
    providers: PermissionFormProvider<any>[];
    setProviderPermissions: (providerKey: string, providerPermissions: object) => void;
    permissions: PermissionsByProviderKey;
};

export const Permissions: FC<Props> = ({appName, providers, setProviderPermissions, permissions}) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <AppTitle>{appName}</AppTitle>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.apps.wizard.permission.helper')}</p>
                <Link href={'https://help.akeneo.com/'}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.permission.helper_link')}
                </Link>
            </Helper>
            {providers.map(provider => {
                const readOnly = false === permissions[provider.key];
                const providerPermissions = false === permissions[provider.key] ? undefined : permissions[provider.key];
                const handlePermissionsChange = (providerPermissions: object) => {
                    setProviderPermissions(provider.key, providerPermissions);
                };

                return (
                    <PermissionsForm
                        key={provider.key}
                        provider={provider}
                        onPermissionsChange={handlePermissionsChange}
                        permissions={providerPermissions}
                        readOnly={readOnly}
                    />
                );
            })}
        </InfoContainer>
    );
};
