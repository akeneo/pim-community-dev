import React, {MouseEvent} from 'react';
import {SupplierRow, SUPPLIERS_PER_PAGE} from '../hooks/useSuppliers';
import {CityIllustration, DeleteIcon, EditIcon, Pagination, onboarderTheme, Table, Search} from 'akeneo-design-system';
import {NoDataSection, NoDataText, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierList} from './EmptySupplierList';

type SupplierListProps = {
    suppliers: SupplierRow[];
    onSearchChange: (value: string) => void;
    searchValue: string;
    totalSuppliers: number;
    onChangePage: (pageNumber: number) => void;
    currentPage: number;
};

const SupplierList = ({
    suppliers,
    onSearchChange,
    searchValue,
    totalSuppliers,
    onChangePage,
    currentPage,
}: SupplierListProps) => {
    const translate = useTranslate();
    const router = useRouter();

    const goToSupplier = (identifier: string, event: MouseEvent<HTMLTableRowElement>) => {
        if (event.metaKey || event.ctrlKey) {
            const newTab = window.open(`#${router.generate('onboarder_serenity_supplier_edit', {identifier})}`, '_blank');
            newTab?.focus();

            return;
        }
        router.redirectToRoute('onboarder_serenity_supplier_edit', {identifier});
    }

    return (
        <>
            <Search
                onSearchChange={onSearchChange}
                searchValue={searchValue}
                placeholder={translate('onboarder.supplier.supplier_list.search_by_supplier')}
            />

            {0 === totalSuppliers && '' !== searchValue && (
                <StyledNoDataSection>
                    <CityIllustration size={256} />
                    <StyledNoDataText>
                        {translate('onboarder.supplier.supplier_list.no_search_result')}
                    </StyledNoDataText>
                </StyledNoDataSection>
            )}
            {0 === totalSuppliers && '' === searchValue && <EmptySupplierList onSupplierCreated={() => {}} />}
            {0 < totalSuppliers && (
                <>
                    <Pagination
                        followPage={onChangePage}
                        currentPage={currentPage}
                        totalItems={totalSuppliers}
                        itemsPerPage={SUPPLIERS_PER_PAGE}
                    />

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_list.columns.supplier')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_list.columns.number_of_contributors')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('onboarder.supplier.supplier_list.columns.actions')}
                            </Table.HeaderCell>
                        </Table.Header>
                        <Table.Body>
                            {suppliers.map((supplier: SupplierRow) => (
                                <Table.Row key={supplier.code} onClick={(event: MouseEvent<HTMLTableRowElement>) => goToSupplier(supplier.identifier, event)} data-testid={supplier.code}>
                                    <Table.Cell>{supplier.label}</Table.Cell>
                                    <Table.Cell>{supplier.contributorsCount}</Table.Cell>
                                    <Table.ActionCell>
                                        <StyledEditIcon color={onboarderTheme.color.grey100} />
                                        <StyledDeleteIcon color={onboarderTheme.color.grey100} />
                                    </Table.ActionCell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </>
            )}
        </>
    );
};

const StyledEditIcon = styled(EditIcon)`
    cursor: pointer;
    margin-right: 20px;
`;
const StyledDeleteIcon = styled(DeleteIcon)`
    cursor: pointer;
`;
const StyledNoDataText = styled(NoDataText)`
    font-size: 13px;
`;
const StyledNoDataSection = styled(NoDataSection)`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

export {SupplierList};
