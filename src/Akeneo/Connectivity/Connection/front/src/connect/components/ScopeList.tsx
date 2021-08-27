import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {
    AddAttributeIcon, AssociateIcon,
    CategoryIcon,
    getColor,
    getFontSize,
    GroupsIcon,
    LocaleIcon, ProductIcon,
    ShopIcon
} from 'akeneo-design-system';
import ScopeMessage from '../../model/Apps/scope-message';

export const ScopeItem = styled.li`
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
    scopeMessages: ScopeMessage[];
}

export const ScopeList: FC<Props> = ({scopeMessages}) => {
    const translate = useTranslate();

    return (
        <ul>
            {
                scopeMessages.map((scopeMessage, key) => {
                    const entities = translate(
                        `akeneo_connectivity.connection.connect.apps.scope.entities.${scopeMessage.entities}`
                    );
                    const Icon = iconsMap[scopeMessage.icon];

                    return (
                        <ScopeItem key={key}>
                            <Icon title={entities} size={24}/>
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: translate(
                                        `akeneo_connectivity.connection.connect.apps.scope.type.${scopeMessage.type}`,
                                        {entities: `<span class='AknConnectivityConnection-helper--highlight'>${entities}</span>`}
                                    ),
                                }}
                            />
                        </ScopeItem>
                    );
                })
            }
        </ul>
    );
};
