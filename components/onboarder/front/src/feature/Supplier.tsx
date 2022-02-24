import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, PageContent, PageHeader, PimView} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useSuppliers} from './hooks/useSuppliers';
import {SupplierList} from './components/SupplierList';
import {EmptySupplierList} from './components/EmptySupplierList';

const Container = styled.div``;

const Supplier = () => {
    const translate = useTranslate();
    const [suppliers, refreshSuppliers] = useSuppliers('', 1);

    return (
        <Container>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.root')}</Breadcrumb.Step>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.suppliers')}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Title>{translate('onboarder.supplier.title')}</PageHeader.Title>
            </PageHeader>
            <PageContent>
                {0 === suppliers.length ? <EmptySupplierList /> : <SupplierList suppliers={suppliers} />}
            </PageContent>
        </Container>
    );
};

export {Supplier};
