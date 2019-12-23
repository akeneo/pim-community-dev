import React, {useContext, useEffect} from 'react';
import {useHistory} from 'react-router';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {NoApp} from '../components/NoApp';
import {
    ApplyButton,
    Breadcrumb,
    BreadcrumbItem,
    Helper,
    HelperLink,
    HelperTitle,
    PageContent,
    PageHeader,
} from '../../common';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';
import {appsFetched} from '../actions/apps-actions';
import {useAppsState} from '../app-state-context';
import {AppGrid} from '../components/AppGrid';
import {App} from '../../../domain/apps/app.interface';

const MAXIMUM_NUMBER_OF_ALLOWED_APPS = 50;

type ResultValue = Array<App>;

export const AppList = () => {
    const history = useHistory();
    const translate = useContext(TranslateContext);

    const [apps, dispatch] = useAppsState();

    const route = useRoute('akeneo_apps_list_rest');
    useEffect(() => {
        fetchResult<ResultValue, never>(route).then(result => isOk(result) && dispatch(appsFetched(result.value)));
    }, [route]);

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
            disabled={Object.keys(apps).length >= MAXIMUM_NUMBER_OF_ALLOWED_APPS}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.create' />
        </ApplyButton>
    );

    const dataSourceApps = Object.values(apps).filter(app => FlowType.DATA_SOURCE === app.flowType);
    const dataDestinationApps = Object.values(apps).filter(app => FlowType.DATA_DESTINATION === app.flowType);
    const otherApps = Object.values(apps).filter(app => FlowType.OTHER === app.flowType);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} buttons={[createButton]} userButtons={userButtons}>
                <Translate id='pim_menu.item.apps' />
            </PageHeader>

            <PageContent>
                <Helper>
                    <HelperTitle>
                        <Translate id='pim_apps.helper.title' />
                    </HelperTitle>
                    <p>
                        <Translate id='pim_apps.helper.description' />
                    </p>
                    <HelperLink href={translate('pim_apps.helper.link_url')} target='_blank'>
                        <Translate id='pim_apps.helper.link' />
                    </HelperLink>
                </Helper>

                {Object.keys(apps).length === 0 ? (
                    <NoApp onCreate={handleCreate} />
                ) : (
                    <>
                        {dataSourceApps && dataSourceApps.length > 0 && (
                            <AppGrid
                                apps={dataSourceApps}
                                title={<Translate id='pim_apps.flow_type.data_source' count={dataSourceApps.length} />}
                            />
                        )}
                        {dataDestinationApps && dataDestinationApps.length > 0 && (
                            <AppGrid
                                apps={dataDestinationApps}
                                title={
                                    <Translate
                                        id='pim_apps.flow_type.data_destination'
                                        count={dataDestinationApps.length}
                                    />
                                }
                            />
                        )}
                        {otherApps && otherApps.length > 0 && (
                            <AppGrid
                                apps={otherApps}
                                title={<Translate id='pim_apps.flow_type.other' count={otherApps.length} />}
                            />
                        )}
                    </>
                )}
            </PageContent>
        </>
    );
};
