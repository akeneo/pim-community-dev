import React, {FC, useRef} from 'react';
import {AppIllustration, Breadcrumb} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../../shared/translate';
import {ConnectedApp} from '../../../../model/Apps/connected-app';
import {Catalog} from '../../../../model/Apps/catalog';
import {useRouter} from '../../../../shared/router/use-router';
import {ApplyButton, PageContent, PageHeader} from '../../../../common';
import {UserButtons} from '../../../../shared/user';
import {DeveloperModeTag} from '../../DeveloperModeTag';
import {CatalogEdit} from '@akeneo-pim-community/catalogs';

type Props = {
    connectedApp: ConnectedApp;
    catalog: Catalog;
};

export const ConnectedAppCatalogContainer: FC<Props> = ({connectedApp, catalog}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const connectedAppHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
        connectionCode: connectedApp.connection_code,
    })}`;
    const catalogEditRef = useRef();

    const SaveButton = () => {
        return (
            <ApplyButton
                onClick={() => {
                    catalogEditRef.current.save();
                }}
                classNames={['AknButtonList-item']}
            >
                <Translate id='pim_common.save' />
            </ApplyButton>
        );
    };

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppHref}>{connectedApp.name}</Breadcrumb.Step>
            <Breadcrumb.Step>{catalog.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const tag = connectedApp.is_test_app ? <DeveloperModeTag /> : null;

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                buttons={[<SaveButton key={0} />]}
                userButtons={<UserButtons />}
                imageSrc={connectedApp.logo ?? undefined}
                imageIllustration={connectedApp.logo ? undefined : <AppIllustration />}
                tag={tag}
            >
                {catalog.name}
            </PageHeader>

            <PageContent>
                <CatalogEdit id={catalog.id} ref={catalogEditRef} />
            </PageContent>
        </>
    );
};
