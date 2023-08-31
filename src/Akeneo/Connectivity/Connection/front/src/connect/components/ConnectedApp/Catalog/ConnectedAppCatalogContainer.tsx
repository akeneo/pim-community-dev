import React, {FC, MutableRefObject, useLayoutEffect, useRef, useState} from 'react';
import {AppIllustration, Breadcrumb} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../../shared/translate';
import {ConnectedApp} from '../../../../model/Apps/connected-app';
import {Catalog} from '../../../../model/Apps/catalog';
import {useRouter} from '../../../../shared/router/use-router';
import {ApplyButton, PageContent, PageHeader} from '../../../../common';
import {UserButtons} from '../../../../shared/user';
import {CatalogEdit, useCatalogForm} from '@akeneo-pim-community/catalogs';
import {NotificationLevel, useNotify} from '../../../../shared/notify';
import {OpenAppButton} from '../OpenAppButton';

type Props = {
    connectedApp: ConnectedApp;
    catalog: Catalog;
};

export const ConnectedAppCatalogContainer: FC<Props> = ({connectedApp, catalog}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const notify = useNotify();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const connectedAppHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
        connectionCode: connectedApp.connection_code,
    })}`;
    const [form, save, isDirty] = useCatalogForm(catalog.id);

    const ref = useRef<HTMLDivElement>() as MutableRefObject<HTMLDivElement>;
    const [headerContextContainer, setHeaderContextContainer] = useState<HTMLDivElement | undefined>(undefined);
    useLayoutEffect(() => {
        setHeaderContextContainer(ref.current);
    });

    const handleSave = async () => {
        try {
            const success = await save();

            const message = success
                ? 'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.success'
                : 'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.error';
            notify(success ? NotificationLevel.SUCCESS : NotificationLevel.ERROR, translate(message));
        } catch (error) {
            notify(
                NotificationLevel.ERROR,
                translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.unknown_error'
                )
            );
        }
    };

    const SaveButton = () => {
        return (
            <ApplyButton onClick={handleSave} disabled={!isDirty} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.save' />
            </ApplyButton>
        );
    };

    const FormState = () => {
        return (
            (isDirty && (
                <div className='updated-status'>
                    <span className='AknState'>
                        <Translate id='pim_common.entity_updated' />
                    </span>
                </div>
            )) ||
            null
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

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                buttons={[<OpenAppButton connectedApp={connectedApp} key={1} />, <SaveButton key={0} />]}
                userButtons={<UserButtons />}
                state={<FormState />}
                imageSrc={connectedApp.logo ?? undefined}
                imageIllustration={connectedApp.logo ? undefined : <AppIllustration />}
                contextContainer={<div ref={ref} />}
            >
                {catalog.name}
            </PageHeader>

            <PageContent pageHeaderHeight={202}>
                {form && (
                    <CatalogEdit
                        id={catalog.id}
                        form={form}
                        headerContextContainer={headerContextContainer}
                    />
                )}
            </PageContent>
        </>
    );
};
