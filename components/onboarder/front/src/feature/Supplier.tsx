import React, {useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, PageContent, PageHeader, PimView} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useSuppliers} from './hooks/useSuppliers';
import {SupplierList} from './components/SupplierList';
import {EmptySupplierList} from './components/EmptySupplierList';

const Container = styled.div``;

const Supplier = () => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const [page, setPage] = useState<number>(0);
    const [suppliers, totalSuppliers, refreshSuppliers] = useSuppliers(searchValue, page);

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
                {
                    0 === suppliers.length && '' === searchValue ?
                    <EmptySupplierList onSupplierCreated={refreshSuppliers} /> :
                    <SupplierList
                        suppliers={suppliers}
                        onSearchChange={setSearchValue}
                        searchValue={searchValue}
                        totalSuppliers={totalSuppliers}
                        onChangePage={setPage}
                        currentPage={page}
                    />
                }
            </PageContent>
        </Container>
    );
};

export {Supplier};
