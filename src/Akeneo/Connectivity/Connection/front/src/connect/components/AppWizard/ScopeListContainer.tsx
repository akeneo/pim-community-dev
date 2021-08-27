import React, {FC} from 'react';
import styled from 'styled-components';
import {
    getColor,
    getFontSize,
    Link,
    ProductIcon,
    CheckRoundIcon,
    AddAttributeIcon,
    AssociateIcon,
    ShopIcon,
    CategoryIcon,
    LocaleIcon,
    GroupsIcon,
} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ScopeItem, ScopeList} from '../ScopeList';
import ScopeMessage from '../../../model/Apps/scope-message';

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
    margin: 10px 0 19px 0;
    width: 280px;
`;

interface Props {
    appName: string;
    scopeMessages: ScopeMessage[];
}

export const ScopeListContainer: FC<Props> = ({appName, scopeMessages}) => {
    const translate = useTranslate();

    const title =
        scopeMessages.length === 0
            ? translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope_title', {app_name: appName})
            : translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.title', {app_name: appName});

    return (
        <>
            <AppTitle>{title}</AppTitle>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')}</p>
                <Link href={'https://help.akeneo.com/'}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper_link')}
                </Link>
            </Helper>
            {
                0 === scopeMessages.length ?
                    <ScopeItem key='0'>
                        <CheckRoundIcon size={24} title={translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope')} />
                        {translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope')}
                    </ScopeItem>
                    :
                    <ScopeList scopeMessages={scopeMessages} />
            }
        </>
    );
};
