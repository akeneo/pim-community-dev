import * as React from 'react';
import {useHistory, useParams} from 'react-router';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Header, Page} from '../../common';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {AppForm} from '../components/AppForm';

export const EditApp = () => {
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

    const saveButton = (
        <ApplyButton onClick={() => console.log('SAVE')} classNames={['AknButtonList-item']}>
            <Translate id='TRANSLATION_KEY.SAVE' />
        </ApplyButton>
    );

    return (
        <Page>
            <Header breadcrumb={breadcrumb} buttons={[saveButton]}>
                <Translate id='pim_menu.item.apps' />
            </Header>
            EditApp {appId}
            <AppForm />
        </Page>
    );
};
