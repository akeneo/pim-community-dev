import React, {useEffect, useState} from 'react';
import {Breadcrumb, Dropdown, IconButton, Link, MoreIcon, useBooleanState, useDebounce} from 'akeneo-design-system';
import {useTranslate, useRoute, PageContent, PageHeader, PimView} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useSuppliers} from './hooks';
import {SupplierList} from './components/SupplierList';
import {EmptySupplierList} from './components/EmptySupplierList';
import {CreateSupplier} from './components/CreateSupplier';

const Container = styled.div``;

const SecondaryActions = () => {
    const translate = useTranslate();
    const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
    const exportRoute = useRoute('onboarder_serenity_supplier_export');

    return (
        <Dropdown>
            <IconButton
                title={translate('pim_common.other_actions')}
                icon={<MoreIcon />}
                level="tertiary"
                ghost="borderless"
                onClick={openDropdown}
            />
            {isDropdownOpen && (
                <Dropdown.Overlay onClose={closeDropdown}>
                    <Dropdown.Header>
                        <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                        <Dropdown.Item onClick={() => {}}>
                            <Link
                                href={exportRoute}
                            >
                                {translate('onboarder.supplier.supplier_list.dropdown.export_suppliers')}
                            </Link>
                        </Dropdown.Item>
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
};

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
                    <SecondaryActions />
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
