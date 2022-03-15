import React from 'react';
import {SupplierRow, SUPPLIERS_PER_PAGE} from '../hooks/useSuppliers';
import {CityIllustration, EditIcon, Pagination, onboarderTheme, Table, Search} from 'akeneo-design-system';
import {NoDataSection, NoDataText, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierList} from './EmptySupplierList';
import {DeleteSupplier} from "./DeleteSupplier";

type SupplierListProps = {
    suppliers: SupplierRow[];
    onSearchChange: (value: string) => void;
    searchValue: string;
    totalSuppliers: number;
    onChangePage: (pageNumber: number) => void;
    currentPage: number;
    onSupplierDeleted: () => void;
};

const SupplierList = ({
    suppliers,
    onSearchChange,
    searchValue,
    totalSuppliers,
    onChangePage,
    currentPage,
    onSupplierDeleted,
}: SupplierListProps) => {
    const translate = useTranslate();

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
                                <Table.Row key={supplier.code} data-testid={supplier.code}>
                                    <Table.Cell>{supplier.label}</Table.Cell>
                                    <Table.Cell>{supplier.contributorsCount}</Table.Cell>
                                    <Table.ActionCell>
                                        <StyledEditIcon color={onboarderTheme.color.grey100} />
                                        <DeleteSupplier identifier={supplier.identifier} onSupplierDeleted={onSupplierDeleted}/>
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
