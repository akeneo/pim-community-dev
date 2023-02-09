import React, {FC, useContext, useEffect, useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../shared/translate';
import {ApplyButton, PageContent, PageHeader} from '../../common';
import {UserButtons, UserContext} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';
import {useHistory} from 'react-router';
import {useFetchExtensions} from '../hooks/use-fetch-extensions';
import {Extensions} from '../../model/extension';
import {Apps} from '../../model/app';
import {UnreachableMarketplace} from '../components/UnreachableMarketplace';
import {Marketplace} from '../components/Marketplace';
import {MarketplaceIsLoading} from '../components/MarketplaceIsLoading';
import {useFetchApps} from '../hooks/use-fetch-apps';
import {useFeatureFlags} from '../../shared/feature-flags';
import {DeveloperModeTag} from '../components/DeveloperModeTag';
import {useTestApps} from '../hooks/use-test-apps';
import {useAppDeveloperMode} from '../hooks/use-app-developer-mode';

export const MarketplacePage: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const history = useHistory();
    const featureFlag = useFeatureFlags();
    const generateUrl = useRouter();
    const fetchExtensions = useFetchExtensions();
    const fetchApps = useFetchApps();
    const isAppDeveloperModeEnabled = useAppDeveloperMode();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const [userProfile, setUserProfile] = useState<string | null>(null);
    const [extensions, setExtensions] = useState<Extensions | null | false>(null);
    const [apps, setApps] = useState<Apps | null | false>(null);
    const {isLoading: isTestAppsLoading, testApps} = useTestApps();

    useEffect(() => {
        const profile = user.get<string | null>('profile');
        if (null === profile) {
            history.push('/connect/app-store/profile');
        } else {
            setUserProfile(profile);
        }
    }, [user]);
    useEffect(() => {
        fetchExtensions()
            .then(setExtensions)
            .catch(() => setExtensions(false));
    }, []);
    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setApps({
                total: 0,
                apps: [],
            });
            return;
        }

        fetchApps()
            .then(setApps)
            .catch(() => setApps(false));
    }, []);

    if (null === userProfile) {
        return null;
    }

    const isLoading = null === extensions || null === apps || isTestAppsLoading;
    const isUnreachable = false === extensions || false === apps;
    const handleCreateTestApp = () => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_custom_apps_create'));
    };

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const tag = isAppDeveloperModeEnabled ? <DeveloperModeTag /> : null;

    const CreateTestAppButton = () => {
        return isAppDeveloperModeEnabled ? (
            <ApplyButton classNames={['AknButtonList-item']} onClick={handleCreateTestApp}>
                <Translate id='akeneo_connectivity.connection.connect.marketplace.test_apps.create_a_custom_app' />
            </ApplyButton>
        ) : null;
    };

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                buttons={[<CreateTestAppButton key={0} />]}
                userButtons={<UserButtons />}
                tag={tag}
            >
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                {isLoading && <MarketplaceIsLoading />}
                {isUnreachable && <UnreachableMarketplace />}
                {!!extensions && !!apps && <Marketplace extensions={extensions} apps={apps} testApps={testApps} />}
            </PageContent>
        </>
    );
};
