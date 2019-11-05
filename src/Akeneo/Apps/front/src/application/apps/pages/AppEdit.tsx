import React from 'react';
import {useHistory, useParams} from 'react-router';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {ApplyButton, Breadcrumb, BreadcrumbItem, PageHeader, Page} from '../../common';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';

export const AppEdit = () => {
    const {appId} = useParams();
    const history = useHistory();

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                <Translate id='pim_menu.tab.system' />
            </BreadcrumbRouterLink>
            <BreadcrumbItem onClick={() => history.push('/apps')}>
                <Translate id='pim_menu.item.apps' />
            </BreadcrumbItem>
            <BreadcrumbItem>
                <Translate id='TRANSLATION_KEY.EDIT_APP' />
            </BreadcrumbItem>
        </Breadcrumb>
    );

    const userButtons = (
        <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-apps-user-navigation'
        />
    );

    const saveButton = (
        <ApplyButton onClick={() => console.log('SAVE')} classNames={['AknButtonList-item']}>
            <Translate id='TRANSLATION_KEY.SAVE' />
        </ApplyButton>
    );

    return (
        <Page>
            <PageHeader breadcrumb={breadcrumb} buttons={[saveButton]} userButtons={userButtons}>
                <Translate id='pim_menu.item.apps' />
            </PageHeader>
            EditApp {appId}
        </Page>
    );
};
