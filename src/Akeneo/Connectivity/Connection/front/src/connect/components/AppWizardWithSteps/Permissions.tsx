import React, {FC, useEffect, useState, useCallback} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/connectivity-connection/src/shared/translate';
import {usePermissionFormRegistry, PermissionFormProvider} from '../../../shared/permission-form-registry';

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
    margin: 0 0 6px 0;
`;

const AppTitle = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
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

type Permissions = {
    [key: string]: any;
};

type RowProps = {
    provider: PermissionFormProvider<any>;
    setPermissions: (state: any) => void;
};

const PermissionRow: FC<RowProps> = React.memo(({provider, setPermissions}) => {
    const handleChange = useCallback(
        (state: any) => {
            setPermissions((permissions: Permissions) => ({...permissions, [provider.key]: state}));
        },
        [setPermissions]
    );

    return <div>{provider.renderForm(handleChange)}</div>;
});

type Props = {
    appName: string;
};

export const Permissions: FC<Props> = ({appName}) => {
    const translate = useTranslate();
    const permissionFormRegistry = usePermissionFormRegistry();
    const [providers, setProviders] = useState<PermissionFormProvider<any>[]>([]);
    const [permissions, setPermissions] = useState<Permissions>({});

    useEffect(() => {
        permissionFormRegistry.all().then(providers => setProviders(providers));
    }, []);

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
            {providers.map(provider => (
                <PermissionRow key={provider.key} provider={provider} setPermissions={setPermissions} />
            ))}
        </InfoContainer>
    );
};
