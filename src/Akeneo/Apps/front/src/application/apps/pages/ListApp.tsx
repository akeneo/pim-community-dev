import * as React from 'react';
import {Link} from 'react-router-dom';
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

    const createButton = (
        <ApplyButton onClick={() => console.log('CREATE')} classNames={['AknButtonList-item']}>
            <Translate id='TRANSLATION_KEY.CREATE' />
        </ApplyButton>
    );

    return (
        <Page>
            <Header breadcrumb={breadcrumb} buttons={[createButton]}>
                <Translate id='pim_menu.item.apps' />
            </Header>
            ListApp
            <Link to='/apps/1'>Edit</Link>
        </Page>
    );
};
