import * as React from 'react';
import {Link} from 'react-router-dom';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Header, Page} from '../../common';
import {RouterContext} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';

export const ListApp = () => {
    const translate = React.useContext(TranslateContext);
    const router = React.useContext(RouterContext);

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbItem
                label={translate('pim_menu.tab.system')}
                onClick={() => router.redirect(router.generate('oro_config_configuration_system'))}
            />
            <BreadcrumbItem label={translate('pim_menu.item.apps')} onClick={() => undefined} isLast={false} />
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
