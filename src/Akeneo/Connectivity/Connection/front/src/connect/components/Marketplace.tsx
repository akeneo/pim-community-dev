import React, {FC, useMemo, useRef, useState} from 'react';
import {MarketplaceHelper} from './MarketplaceHelper';
import {ArrowSimpleUpIcon, getColor, IconButton, Search} from 'akeneo-design-system';
import {MarketplaceCard} from './MarketplaceCard';
import {Extension, Extensions} from '../../model/extension';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../shared/scroll/utils/findScrollParent';
import {App, Apps, CustomApps} from '../../model/app';
import {Section} from './Section';
import {ActivateAppButton} from './ActivateAppButton';
import {useFeatureFlags} from '../../shared/feature-flags';
import {useConnectionsLimitReached} from '../../shared/hooks/use-connections-limit-reached';
import {CustomAppList} from './CustomApps/CustomAppList';
import {useSecurity} from '../../shared/security';

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

const SearchBar = styled(Search)`
    margin-top: 20px;
    margin-bottom: 10px;
`;

type Props = {
    extensions: Extensions;
    apps: Apps;
    customApps: CustomApps;
};

export const Marketplace: FC<Props> = ({extensions, apps, customApps}) => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const ref = useRef(null);
    const scrollContainer = findScrollParent(ref.current);
    const displayScrollButton = useDisplayScrollTopButton(ref);
    const security = useSecurity();
    const isManageAppsAuthorized = security.isGranted('akeneo_connectivity_connection_manage_apps');
    const isLimitReached = useConnectionsLimitReached();
    const [search, setSearch] = useState<string>('');

    const extensionsComponents: {[key: string]: JSX.Element} = useMemo(
        () =>
            extensions.extensions.reduce((accumulator, extension) => {
                return {...accumulator, [extension.id]: <MarketplaceCard key={extension.id} item={extension} />};
            }, {}),
        [extensions]
    );

    const extensionsList = extensions.extensions
        .filter(extension => '' === search || extension.name.toLowerCase().includes(search.toLowerCase()))
        .map((extension: Extension) => extensionsComponents[extension.id]);

    const appsComponents: {[key: string]: JSX.Element} = useMemo(
        () =>
            apps.apps.reduce((accumulator, app) => {
                return {
                    ...accumulator,
                    [app.id]: (
                        <MarketplaceCard
                            key={app.id}
                            item={app}
                            additionalActions={[
                                <ActivateAppButton
                                    key={1}
                                    id={app.id}
                                    isConnected={app.connected}
                                    isPending={app.isPending}
                                    isDisabled={!isManageAppsAuthorized || isLimitReached}
                                />,
                            ]}
                        />
                    ),
                };
            }, {}),
        [apps, isLimitReached, isManageAppsAuthorized]
    );

    const appsList = apps.apps
        .filter(app => '' === search || app.name.toLowerCase().includes(search.toLowerCase()))
        .map((app: App) => appsComponents[app.id]);

    const filteredCustomApps = customApps.apps.filter(
        app => '' === search || app.name.toLowerCase().includes(search.toLowerCase())
    );

    const customAppsList = {
        total: filteredCustomApps.length,
        apps: filteredCustomApps,
    };

    const searchCount = extensionsList.length + appsList.length + filteredCustomApps.length;

    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
            <MarketplaceHelper count={extensions.total + apps.total + customApps.total} />

            <SearchBar
                onSearchChange={setSearch}
                placeholder={translate('akeneo_connectivity.connection.connect.marketplace.search.placeholder')}
                searchValue={search}
                title={translate('akeneo_connectivity.connection.connect.marketplace.search.placeholder')}
            >
                <span>
                    {translate(
                        'akeneo_connectivity.connection.connect.marketplace.search.total',
                        {
                            total: searchCount.toString(),
                        },
                        searchCount
                    )}
                </span>
            </SearchBar>

            {customApps.total > 0 && (
                <CustomAppList customApps={customAppsList} isConnectLimitReached={isLimitReached} />
            )}

            {featureFlag.isEnabled('marketplace_activate') && (
                <Section
                    title={translate('akeneo_connectivity.connection.connect.marketplace.apps.title')}
                    information={translate(
                        'akeneo_connectivity.connection.connect.marketplace.apps.total',
                        {
                            total: appsList.length.toString(),
                        },
                        appsList.length
                    )}
                    emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.apps.empty')}
                    warningMessage={
                        !isLimitReached
                            ? null
                            : translate(
                                  'akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached'
                              )
                    }
                >
                    {appsList}
                </Section>
            )}
            <Section
                title={translate('akeneo_connectivity.connection.connect.marketplace.extensions.title')}
                information={translate(
                    'akeneo_connectivity.connection.connect.marketplace.extensions.total',
                    {
                        total: extensionsList.length.toString(),
                    },
                    extensionsList.length
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
