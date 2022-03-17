import React from 'react';
import {Breadcrumb, Button, TabBar, useTabBar} from 'akeneo-design-system';
import {useTranslate, PageContent, PageHeader, PimView} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Supplier} from './models';
import {Configuration} from './components/SupplierEdit/Configuration';
import {Contributors} from './components/SupplierEdit/Contributors';

const SupplierEdit = () => {
    const translate = useTranslate();
    const [isCurrent, switchTo] = useTabBar('configuration');
    const supplier: Supplier = {
        identifier: '',
        code: 'mysupplier',
        label: 'My Supplier',
        contributors: [],
    };

    return (
        <Container>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.root')}</Breadcrumb.Step>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.suppliers')}</Breadcrumb.Step>
                        <Breadcrumb.Step>{supplier.label}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Actions>
                    <Button level={'primary'}>{translate('pim_common.save')}</Button>
                </PageHeader.Actions>
                <PageHeader.Title>{supplier.label}</PageHeader.Title>
            </PageHeader>
            <StyledPageContent>
                <TabBar moreButtonTitle="More">
                    <TabBar.Tab isActive={isCurrent('configuration')} onClick={() => switchTo('configuration')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.configuration')}
                    </TabBar.Tab>
                    <TabBar.Tab isActive={isCurrent('contributors')} onClick={() => switchTo('contributors')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.contributors')}
                    </TabBar.Tab>
                    <TabBar.Tab isActive={isCurrent('product_files')} onClick={() => switchTo('product_files')}>
                        {translate('onboarder.supplier.supplier_edit.tabs.product_files')}
                    </TabBar.Tab>
                </TabBar>
                {isCurrent('configuration') && <Configuration supplier={supplier} />}
                {isCurrent('contributors') && <Contributors contributors={[{email: 'foo@foo.bar'}]} />}
            </StyledPageContent>
        </Container>
    );
};

const Container = styled.div``;

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

export {SupplierEdit};
