import React from 'react';
import {SupplierRow} from '../hooks/useSuppliers';
import {Table} from 'akeneo-design-system';

type SupplierListProps = {
    suppliers: SupplierRow[];
};

const SupplierList = ({suppliers}: SupplierListProps) => {
    return (
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
                        <Table.ActionCell></Table.ActionCell>
                    </Table.Row>
                ))}
            </Table.Body>
        </Table>
    );
};

export {SupplierList};
