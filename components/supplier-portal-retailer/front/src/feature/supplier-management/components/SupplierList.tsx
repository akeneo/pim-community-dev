import React, {useState} from 'react';
import {SupplierRow, SUPPLIERS_PER_PAGE} from '../hooks';
import {
    CityIllustration,
    DeleteIcon,
    EditIcon,
    getColor,
    Pagination,
    pimTheme,
    Search,
    Table,
    useBooleanState,
} from 'akeneo-design-system';
import {NoDataSection, NoDataText, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierList} from './EmptySupplierList';
import {DeleteSupplier} from './DeleteSupplier';
import {useHistory} from 'react-router';

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
    const history = useHistory();
    const [isModalOpen, openModal, closeModal] = useBooleanState(false);
    const [supplierIdentifierToDelete, setSupplierIdentifierToDelete] = useState<string>('');

    const goToSupplier = (identifier: string) => {
        history.push(`/supplier/${identifier}`);
    };

    const handleOpenModal = (event: any, supplierIdentifier: string) => {
        event.stopPropagation();
        setSupplierIdentifierToDelete(supplierIdentifier);
        openModal();
    };

    return (
        <>
            <StyledSearch
                onSearchChange={onSearchChange}
                searchValue={searchValue}
                placeholder={translate('supplier_portal.supplier.supplier_list.search_by_supplier')}
            />

            {0 === totalSuppliers && '' !== searchValue && (
                <StyledNoDataSection>
                    <CityIllustration size={256} />
                    <StyledNoDataText>
                        {translate('supplier_portal.supplier.supplier_list.no_search_result')}
                    </StyledNoDataText>
                </StyledNoDataSection>
            )}
            {0 === totalSuppliers && '' === searchValue && <EmptySupplierList onSupplierCreated={() => {}} />}
            {0 < totalSuppliers && (
                <>
                    <Pagination
                        followPage={onChangePage}
                        currentPage={
                            currentPage > totalSuppliers / SUPPLIERS_PER_PAGE
                                ? Math.ceil(totalSuppliers / SUPPLIERS_PER_PAGE)
                                : currentPage
                        }
                        totalItems={totalSuppliers}
                        itemsPerPage={SUPPLIERS_PER_PAGE}
                    />

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('supplier_portal.supplier.supplier_list.columns.supplier')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('supplier_portal.supplier.supplier_list.columns.number_of_contributors')}
                            </Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                        </Table.Header>
                        <Table.Body>
                            {suppliers.map((supplier: SupplierRow) => (
                                <Table.Row
                                    key={supplier.code}
                                    onClick={() => goToSupplier(supplier.identifier)}
                                    data-testid={supplier.code}
                                >
                                    <Table.Cell>{supplier.label}</Table.Cell>
                                    <Table.Cell>{supplier.contributorsCount}</Table.Cell>
                                    <Table.ActionCell>
                                        <StyledEditIcon color={pimTheme.color.grey100} />
                                        <StyledDeleteIcon
                                            color={pimTheme.color.grey100}
                                            title={translate('pim_common.delete')}
                                            onClick={(event: any) => handleOpenModal(event, supplier.identifier)}
                                        />
                                    </Table.ActionCell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </>
            )}
            {isModalOpen && (
                <DeleteSupplier
                    identifier={supplierIdentifierToDelete}
                    onSupplierDeleted={() => {
                        closeModal();
                        onSupplierDeleted();
                    }}
                    onCloseModal={closeModal}
                />
            )}
        </>
    );
};

const StyledSearch = styled(Search)`
    margin-bottom: 10px;
`;

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

const StyledDeleteIcon = styled(DeleteIcon)`
    cursor: pointer;
    color: ${getColor('grey100')};
`;

export {SupplierList};
