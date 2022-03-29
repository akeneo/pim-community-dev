import React, {useEffect, useState} from 'react';
import {Breadcrumb, useDebounce} from 'akeneo-design-system';
import {useTranslate, PageContent, PageHeader, PimView} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useSuppliers} from './hooks';
import {SupplierList} from './components/SupplierList';
import {EmptySupplierList} from './components/EmptySupplierList';
import {CreateSupplier} from './components/CreateSupplier';

const Container = styled.div``;

const SupplierIndex = () => {
    const translate = useTranslate();
    const [searchValue, setSearchValue] = useState('');
    const debouncedSearchValue = useDebounce(searchValue);
    const [page, setPage] = useState<number>(1);
    const [suppliers, totalSuppliers, refreshSuppliers] = useSuppliers(debouncedSearchValue, page);

    useEffect(() => {
        totalSuppliers > 0 && setPage(1);
    }, [totalSuppliers]);

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
                <PageHeader.Actions>
                    <CreateSupplier
                        onSupplierCreated={refreshSuppliers}
                        createButtonlabel={translate('pim_common.create')}
                    />
                </PageHeader.Actions>
                <PageHeader.Title>
                    {translate('onboarder.supplier.supplier_list.title', {count: suppliers.length}, suppliers.length)}
                </PageHeader.Title>
            </PageHeader>
            <StyledPageContent>
                {0 === suppliers.length && '' === searchValue ? (
                    <EmptySupplierList onSupplierCreated={refreshSuppliers} />
                ) : (
                    <SupplierList
                        suppliers={suppliers}
                        onSearchChange={setSearchValue}
                        searchValue={searchValue}
                        totalSuppliers={totalSuppliers}
                        onChangePage={setPage}
                        currentPage={page}
                        onSupplierDeleted={refreshSuppliers}
                    />
                )}
            </StyledPageContent>
        </Container>
    );
};

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

export {SupplierIndex};
