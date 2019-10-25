import React, {useContext} from 'react';
import {useHistory} from 'react-router';
import {App as AppInterface} from '../../../domain/apps/app.interface';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {NoApp} from '../../apps/components/NoApp';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Helper, HelperLink, HelperTitle, Page, PageHeader} from '../../common';
import {isOk} from '../../shared/fetch/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';
import {AppGrid} from '../components/AppGrid';
import {useFetch} from '../../shared/fetch';

const MAXIMUM_NUMBER_OF_ALLOWED_APPS = 50;

export const AppList = () => {
    const history = useHistory();
    const translate = useContext(TranslateContext);

    const result = useFetch<AppInterface[], Error>(useRoute('akeneo_apps_list_rest'));
    const apps = isOk(result) ? result.data : [];

    const handleCreate = () => history.push('/apps/create');

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                <Translate id='pim_menu.tab.system' />
            </BreadcrumbRouterLink>
            <BreadcrumbItem onClick={() => undefined} isLast={false}>
                <Translate id='pim_menu.item.apps' />
            </BreadcrumbItem>
        </Breadcrumb>
    );

    const userButtons = (
        <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-apps-user-navigation'
        />
    );

    const createButton = (
        <ApplyButton
            onClick={handleCreate}
            disabled={!apps || apps.length >= MAXIMUM_NUMBER_OF_ALLOWED_APPS}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.create' />
        </ApplyButton>
    );

    const dataSourceApps = apps && apps.filter(app => FlowType.DATA_SOURCE === app.flowType);
    const dataDestinationApps = apps && apps.filter(app => FlowType.DATA_DESTINATION === app.flowType);
    const otherApps = apps && apps.filter(app => FlowType.OTHER === app.flowType);

    return (
        <Page>
            <PageHeader breadcrumb={breadcrumb} buttons={[createButton]} userButtons={userButtons}>
                <Translate id='pim_menu.item.apps' />
            </PageHeader>

            <Helper>
                <HelperTitle>
                    <Translate id='pim_apps.helper.title' />
                </HelperTitle>
                <p>
                    <Translate id='pim_apps.helper.description' />
                </p>
                <HelperLink href={translate('pim_apps.helper.link_url')}>
                    <Translate id='pim_apps.helper.link' />
                </HelperLink>
            </Helper>

            {dataSourceApps && dataSourceApps.length > 0 && (
                <AppGrid
                    apps={dataSourceApps}
                    title={<Translate id='pim_apps.flow_type.data_source' count={dataSourceApps.length} />}
                />
            )}
            {dataDestinationApps && dataDestinationApps.length > 0 && (
                <AppGrid
                    apps={dataDestinationApps}
                    title={<Translate id='pim_apps.flow_type.data_destination' count={dataDestinationApps.length} />}
                />
            )}
            {otherApps && otherApps.length > 0 && (
                <AppGrid
                    apps={otherApps}
                    title={<Translate id='pim_apps.flow_type.other' count={otherApps.length} />}
                />
            )}

            {apps && apps.length === 0 && <NoApp onCreate={handleCreate} />}
        </Page>
    );
};
