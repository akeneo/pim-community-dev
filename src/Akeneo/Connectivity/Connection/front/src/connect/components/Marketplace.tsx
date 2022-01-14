import React, {FC, useRef} from 'react';
import MarketplaceHelper from './MarketplaceHelper';
import {ArrowSimpleUpIcon, getColor, IconButton} from 'akeneo-design-system';
import {MarketplaceCard} from './MarketplaceCard';
import {Extension, Extensions} from '../../model/extension';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../shared/scroll/utils/findScrollParent';
import {App, Apps, TestApps} from '../../model/app';
import {Section} from './Section';
import {ActivateAppButton} from './ActivateAppButton';
import {useFeatureFlags} from '../../shared/feature-flags';
import {TestAppList} from './TestApp/TestAppList';
import {useSecurity} from '../../shared/security';
import {useDeveloperMode} from '../hooks/use-developer-mode';

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
    extensions: Extensions;
    apps: Apps;
    testApps: TestApps;
};

export const Marketplace: FC<Props> = ({extensions, apps, testApps}) => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const ref = useRef(null);
    const scrollContainer = findScrollParent(ref.current);
    const displayScrollButton = useDisplayScrollTopButton(ref);
    const isDeveloperModeEnabled = useDeveloperMode();
    const security = useSecurity();
    const isManageAppsAuthorized = security.isGranted('akeneo_connectivity_connection_manage_apps');
    const extensionsList = extensions.extensions.map((extension: Extension) => (
        <MarketplaceCard key={extension.id} item={extension} />
    ));
    const appsList = apps.apps.map((app: App) => (
        <MarketplaceCard
            key={app.id}
            item={app}
            additionalActions={[
                <ActivateAppButton
                    key={1}
                    id={app.id}
                    isConnected={app.connected}
                    isDisabled={!isManageAppsAuthorized}
                />,
            ]}
        />
    ));
    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
            <MarketplaceHelper count={extensions.total + apps.total + testApps.total} />

            {isDeveloperModeEnabled && <TestAppList testApps={testApps} />}

            {featureFlag.isEnabled('marketplace_activate') && (
                <Section
                    title={translate('akeneo_connectivity.connection.connect.marketplace.apps.title')}
                    information={translate(
                        'akeneo_connectivity.connection.connect.marketplace.apps.total',
                        {
                            total: apps.total.toString(),
                        },
                        apps.total
                    )}
                    emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.apps.empty')}
                >
                    {appsList}
                </Section>
            )}
            <Section
                title={translate('akeneo_connectivity.connection.connect.marketplace.extensions.title')}
                information={translate(
                    'akeneo_connectivity.connection.connect.marketplace.extensions.total',
                    {
                        total: extensions.total.toString(),
                    },
                    extensions.total
                )}
                emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.extensions.empty')}
            >
                {extensionsList}
            </Section>

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
