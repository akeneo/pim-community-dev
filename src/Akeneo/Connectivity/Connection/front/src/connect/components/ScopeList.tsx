import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {
    AddAttributeIcon,
    AssociateIcon,
    BookIcon,
    CategoryIcon,
    getColor,
    getFontSize,
    GroupsIcon,
    LocaleIcon,
    ProductIcon,
    ShopIcon,
    CheckRoundIcon,
    EntityIcon,
    AssetsIcon,
    AkeneoThemedProps,
    FontSize,
    Badge,
} from 'akeneo-design-system';
import ScopeMessage from '../../model/Apps/scope-message';

export const ScopeItem = styled.li.attrs(
    (props: {fontSize?: keyof FontSize; highlightMode?: 'new' | 'old' | null} & AkeneoThemedProps) => ({
        fontSize: props.fontSize || 'bigger',
        highlightMode: props.highlightMode,
    })
)`
    color: ${props => getColor('grey', props.highlightMode === 'old' ? 120 : 140)};
    font-size: ${props => getFontSize(props.fontSize)};
    font-weight: normal;
    line-height: 24px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;

    & > svg {
        margin-right: 10px;
        color: ${getColor('grey', 100)};
    }
`;

const NewBadge = styled(Badge)`
    margin-left: 10px;
`;

const iconsMap: {[key: string]: React.ElementType} = {
    catalog_structure: GroupsIcon,
    attribute_options: AddAttributeIcon,
    categories: CategoryIcon,
    channel_settings: ShopIcon,
    channel_localization: LocaleIcon,
    association_types: AssociateIcon,
    products: ProductIcon,
    reference_entity: EntityIcon,
    reference_entity_record: EntityIcon,
    asset_families: AssetsIcon,
    assets: AssetsIcon,
    catalogs: BookIcon,
};

interface Props {
    scopeMessages: ScopeMessage[];
    itemFontSize?: string;
    highlightMode?: 'new' | 'old' | null;
}

export const ScopeList: FC<Props> = ({scopeMessages, itemFontSize, highlightMode}) => {
    const translate = useTranslate();

    return (
        <ul data-testid={'scope-list'}>
            {scopeMessages.map((scopeMessage, key) => {
                const entities = translate(
                    `akeneo_connectivity.connection.connect.apps.scope.entities.${scopeMessage.entities}`
                );
                const Icon = iconsMap[scopeMessage.icon] ?? CheckRoundIcon;

                return (
                    <ScopeItem key={key} fontSize={itemFontSize} highlightMode={highlightMode}>
                        <Icon title={entities} size={24} />
                        <div
                            dangerouslySetInnerHTML={{
                                __html: translate(
                                    `akeneo_connectivity.connection.connect.apps.scope.type.${scopeMessage.type}`,
                                    {
                                        entities: `<span class='AknConnectivityConnection-helper--highlight${
                                            'old' === highlightMode ? '--lighter' : ''
                                        }'>
                                                    ${entities}
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
                    </ScopeItem>
                );
            })}
        </ul>
    );
};
