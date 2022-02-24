import React from 'react';
import {SupplierRow, SUPPLIERS_PER_PAGE} from '../hooks/useSuppliers';
import {DeleteIcon, EditIcon, Pagination, pimTheme, Table, Search} from 'akeneo-design-system';
import {useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

type SupplierListProps = {
    suppliers: SupplierRow[];
    onSearchChange: (value: string) => void;
    searchValue: string;
    totalSuppliers: number;
    onChangePage: (pageNumber: number) => void;
    currentPage: number;
};

const SupplierList = ({suppliers, onSearchChange, searchValue, totalSuppliers, onChangePage, currentPage}: SupplierListProps) => {
    const translate = useTranslate();

    return (
        <>
            <Search onSearchChange={onSearchChange} searchValue={searchValue} placeholder={translate('onboarder.supplier.search_by_supplier')}/>

            <Pagination
                followPage={onChangePage}
                currentPage={currentPage}
                totalItems={totalSuppliers}
                itemsPerPage={SUPPLIERS_PER_PAGE}
            />

            <Table>
                <Table.Header>
                    <Table.HeaderCell>Supplier</Table.HeaderCell>
                    <Table.HeaderCell>Number of contributors</Table.HeaderCell>
                    <Table.HeaderCell>Actions</Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {suppliers.map((supplier: SupplierRow) => (
                        <Table.Row key={supplier.code}>
                            <Table.Cell>{supplier.label}</Table.Cell>
                            <Table.Cell>{supplier.contributorsCount}</Table.Cell>
                            <Table.ActionCell>
                                <StyledEditIcon color={pimTheme.color.grey100}/>
                                <StyledDeleteIcon color={pimTheme.color.grey100}/>
                            </Table.ActionCell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
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

export {SupplierList};
