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
import {ScopeMessage} from '../../hooks/use-fetch-app-wizard-data';

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

const ScopeItem = styled.li`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('bigger')};
    font-weight: normal;
    line-height: 21px;
    margin-bottom: 13px;
    display: flex;
    align-items: center;

    & > svg {
        margin-right: 10px;
        color: ${getColor('grey', 100)};
    }
`;

const iconsMap: {[key: string]: React.ElementType} = {
    catalog_structure: GroupsIcon,
    attribute_options: AddAttributeIcon,
    categories: CategoryIcon,
    channel_settings: ShopIcon,
    channel_localization: LocaleIcon,
    association_types: AssociateIcon,
    products: ProductIcon,
};

interface Props {
    appName: string;
    scopeMessages: ScopeMessage[];
}

export const ScopeList: FC<Props> = ({appName, scopeMessages}) => {
    const translate = useTranslate();

    let scopeList = scopeMessages.map((scopeMessage, key) => {
        const entities = translate(
            `akeneo_connectivity.connection.connect.apps.authorize.scope.entities.${scopeMessage.entities}`
        );
        const Icon = iconsMap[scopeMessage.icon] ?? CheckRoundIcon;

        return (
            <ScopeItem key={key}>
                <Icon title={entities} size={24} />
                <div
                    dangerouslySetInnerHTML={{
                        __html: translate(
                            `akeneo_connectivity.connection.connect.apps.authorize.scope.type.${scopeMessage.type}`,
                            {entities: `<span class='AknConnectivityConnection-helper--highlight'>${entities}</span>`}
                        ),
                    }}
                />
            </ScopeItem>
        );
    });

    const title =
        scopeList.length === 0
            ? translate('akeneo_connectivity.connection.connect.apps.authorize.no_scope_title', {app_name: appName})
            : translate('akeneo_connectivity.connection.connect.apps.authorize.title', {app_name: appName});

    if (scopeList.length === 0) {
        const message = translate('akeneo_connectivity.connection.connect.apps.authorize.no_scope');
        scopeList = [
            <ScopeItem key='0'>
                <CheckRoundIcon size={24} title={message} />
                {message}
            </ScopeItem>,
        ];
    }

    return (
        <>
            <AppTitle>{title}</AppTitle>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.apps.authorize.helper')}</p>
                <Link
                    href={
                        'https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#all-editions-authorization-step'
                    }
                >
                    {translate('akeneo_connectivity.connection.connect.apps.authorize.helper_link')}
                </Link>
            </Helper>
            <ul>{scopeList}</ul>
        </>
    );
};
