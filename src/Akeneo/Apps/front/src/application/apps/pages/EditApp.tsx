import * as React from 'react';
import {useHistory} from 'react-router';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Header, Page} from '../../common';
import {RouterContext} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';
import {AppForm} from '../components/AppForm';

export const EditApp = () => {
    const translate = React.useContext(TranslateContext);
    const router = React.useContext(RouterContext);
    const history = useHistory();

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbItem
                label={translate('pim_menu.tab.system')}
                onClick={() => router.redirect(router.generate('oro_config_configuration_system'))}
            />
            <BreadcrumbItem label={translate('pim_menu.item.apps')} onClick={() => history.push('/apps')} />
            <BreadcrumbItem label={translate('TRANSLATION_KEY.EDIT_APP')} />
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
            ListApp
            <AppForm />
        </Page>
    );
};
