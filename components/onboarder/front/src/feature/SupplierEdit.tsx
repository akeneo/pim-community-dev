import React from 'react';
import {Breadcrumb, Button, TabBar, useTabBar} from 'akeneo-design-system';
import {
    PageContent,
    PageHeader,
    PimView,
    UnsavedChanges,
    useTranslate
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Configuration} from './components/SupplierEdit/Configuration';
import {useSupplier} from './hooks';
import {useHistory, useParams} from 'react-router';
import {ContributorList} from './components/SupplierEdit/ContributorList';

const SupplierEdit = () => {
    const translate = useTranslate();
    const [isCurrent, switchTo] = useTabBar('configuration');
    const {supplierIdentifier} = useParams<{supplierIdentifier: string}>();
    const {
        supplierCode,
        supplierLabel,
        supplierContributors,
        setSupplierLabel,
        setSupplierContributors,
        isSupplierLoaded,
        saveSupplier,
        supplierHasChanges,
    } = useSupplier(supplierIdentifier);
    const history = useHistory();

    if (!isSupplierLoaded) {
        return null;
    }

    return (
        <Container>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.root')}</Breadcrumb.Step>
                        <Breadcrumb.Step href={history.createHref({pathname: '/'})}>
                            {translate('onboarder.supplier.breadcrumb.suppliers')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step>{supplierLabel}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Actions>
                    <Button level={'primary'} onClick={saveSupplier}>
                        {translate('pim_common.save')}
                    </Button>
                </PageHeader.Actions>
                <PageHeader.Title>{supplierLabel}</PageHeader.Title>
                <PageHeader.State>{supplierHasChanges() && <UnsavedChanges />}</PageHeader.State>
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
                {isCurrent('configuration') && <Configuration code={supplierCode} label={supplierLabel} setLabel={setSupplierLabel} />}
                {isCurrent('contributors') && <ContributorList contributors={supplierContributors} setContributors={setSupplierContributors} />}
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
