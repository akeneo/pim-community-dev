import React, {FC, useRef} from 'react';
import {ArrowSimpleUpIcon, getColor, IconButton, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../../shared/scroll/utils/findScrollParent';
import {ConnectedApp, ConnectedApps} from '../../../model/connected-app';
import {useFeatureFlags} from '../../../shared/feature-flags';
import ConnectedAppsContainerHelper from "./ConnectedAppsContainerHelper";
import {ConnectedAppCard} from "./ConnectedAppCard";
import {NoConnectedApps} from "./NoConnectedApps";
import {CardGrid} from '../Section';

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
    connectedApps: ConnectedApps;
};

export const ConnectedAppsContainer: FC<Props> = ({connectedApps}) => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const ref = useRef(null);
    const scrollContainer = findScrollParent(ref.current);
    const displayScrollButton = useDisplayScrollTopButton(ref);
    const connectedAppsList = connectedApps.connected_apps.map((connectedApp: ConnectedApp) => (
        <ConnectedAppCard key={connectedApp.id} item={connectedApp} />
    ));
    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
            <ConnectedAppsContainerHelper count={connectedApps.total} />

            {featureFlag.isEnabled('marketplace_activate') && (
                <>
                    <SectionTitle>
                        <SectionTitle.Title>{translate('akeneo_connectivity.connection.connect.connected_apps.apps.title')}</SectionTitle.Title>
                        <SectionTitle.Spacer />
                        <SectionTitle.Information>{translate(
                            'akeneo_connectivity.connection.connect.connected_apps.apps.total',
                            {
                                total: connectedApps.total.toString(),
                            },
                            connectedApps.total
                        )}</SectionTitle.Information>
                    </SectionTitle>

                    {0 === connectedAppsList.length && <NoConnectedApps />}
                    {connectedAppsList.length > 0 && <CardGrid>{connectedAppsList}</CardGrid>}
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
