import * as React from 'react';
import {Link} from 'react-router-dom';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Header, Page} from '../../common';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';

export const ListApp = () => {
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
        <ApplyButton onClick={() => console.log('CREATE')} classNames={['AknButtonList-item']}>
            <Translate id='TRANSLATION_KEY.CREATE' />
        </ApplyButton>
    );

    return (
        <Page>
            <Header breadcrumb={breadcrumb} buttons={[createButton]} userButtons={userButtons}>
                <Translate id='pim_menu.item.apps' />
            </Header>
            ListApp
            <Link to='/apps/1'>Edit</Link>
        </Page>
    );
};
