import React, {FC, useRef} from 'react';
import {ArrowSimpleUpIcon, DangerIcon, getColor, Helper, IconButton, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../../shared/scroll/utils/findScrollParent';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useFeatureFlags} from '../../../shared/feature-flags';
import ConnectedAppsContainerHelper from './ConnectedAppsContainerHelper';
import {ConnectedAppCard} from './ConnectedAppCard';
import {NoConnectedApps} from './NoConnectedApps';
import {CardGrid} from '../Section';
import {ConnectedCustomAppList} from './ConnectedCustomAppList';

const ScrollToTop = styled(IconButton)`
    position: fixed;
    bottom: 40px;
    right: 40px;
    width: 38px;
    height: 38px;
    border-radius: 50%;

    background-color: ${getColor('brand', 100)};
    color: ${getColor('white')};

    &:hover:not([disabled]) {
        background-color: ${getColor('brand', 120)};
    }
`;

type Props = {
    allConnectedApps: ConnectedApp[];
};

export const ConnectedAppsContainer: FC<Props> = ({allConnectedApps}) => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const ref = useRef(null);
    const scrollContainer = findScrollParent(ref.current);
    const displayScrollButton = useDisplayScrollTopButton(ref);

    const connectedCustomApps = allConnectedApps.filter((connectedApp: ConnectedApp) => connectedApp.is_custom_app);
    const connectedApps = allConnectedApps.filter((connectedApp: ConnectedApp) => !connectedApp.is_custom_app);
    const hasPendingApps = undefined !== connectedApps.find((connectedApp: ConnectedApp) => connectedApp.is_pending);

    const atLeastOneAppIsNotListedOnTheAppStore: boolean =
        undefined !== connectedApps.find(connectedApp => false === connectedApp.is_listed_on_the_appstore);

    const connectedAppCards = connectedApps.map((connectedApp: ConnectedApp) => (
        <ConnectedAppCard key={connectedApp.id} item={connectedApp} />
    ));
    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
            <ConnectedAppsContainerHelper count={allConnectedApps.length} />

            <ConnectedCustomAppList connectedCustomApps={connectedCustomApps} />

            {featureFlag.isEnabled('marketplace_activate') && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>
                            {translate('akeneo_connectivity.connection.connect.connected_apps.list.apps.title')}
                        </SectionTitle.Title>
                        <SectionTitle.Spacer />
                        <SectionTitle.Information>
                            {translate(
                                'akeneo_connectivity.connection.connect.connected_apps.list.apps.total',
                                {
                                    total: connectedApps.length.toString(),
                                },
                                connectedApps.length
                            )}
                        </SectionTitle.Information>
                    </SectionTitle>

                    {atLeastOneAppIsNotListedOnTheAppStore && (
                        <Helper level='warning'>
                            {translate(
                                'akeneo_connectivity.connection.connect.connected_apps.list.apps.at_least_one_is_not_listed_on_the_appstore'
                            )}
                        </Helper>
                    )}

                    {hasPendingApps && (
                        <Helper icon={<DangerIcon />} level='warning'>
                            {translate('akeneo_connectivity.connection.connect.connected_apps.list.apps.pending_apps')}
                        </Helper>
                    )}

                    {0 === connectedAppCards.length && <NoConnectedApps />}
                    {connectedAppCards.length > 0 && <CardGrid>{connectedAppCards}</CardGrid>}
                </>
            )}

            {displayScrollButton && (
                <ScrollToTop
                    onClick={handleScrollTop}
                    title={translate('akeneo_connectivity.connection.connect.marketplace.scroll_to_top')}
                    icon={<ArrowSimpleUpIcon />}
                />
            )}
        </>
    );
};
