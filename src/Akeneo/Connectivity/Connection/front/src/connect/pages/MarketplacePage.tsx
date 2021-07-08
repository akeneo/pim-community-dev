import React, {FC, useContext, useEffect, useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader, PageContent} from '../../common';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';
import {UserContext} from '../../shared/user';
import {useHistory} from 'react-router';
import {useFetchExtensions} from '../hooks/use-fetch-extensions';
import {Extensions} from '../../model/extension';
import {UnreachableMarketplace} from '../components/UnreachableMarketplace';
import {Marketplace} from '../components/Marketplace';
import {MarketplaceIsLoading} from '../components/MarketplaceIsLoading';

export const MarketplacePage: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const history = useHistory();
    const generateUrl = useRouter();
    const fetchExtensions = useFetchExtensions();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const [userProfile, setUserProfile] = useState<string | null>(null);
    const [extensions, setExtensions] = useState<Extensions | null | false>(null);

    useEffect(() => {
        const profile = user.get<string | null>('profile');
        if (null === profile) {
            history.push('/connect/marketplace/profile');
        } else {
            setUserProfile(profile);
        }
    }, [user]);
    useEffect(() => {
        fetchExtensions()
            .then(setExtensions)
            .catch(() => setExtensions(false));
    }, [fetchExtensions]);

    if (null === userProfile) {
        return null;
    }

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                {null === extensions && <MarketplaceIsLoading />}
                {false === extensions && <UnreachableMarketplace />}
                {false !== extensions && null !== extensions && <Marketplace extensions={extensions} />}
            </PageContent>
        </>
    );
};
