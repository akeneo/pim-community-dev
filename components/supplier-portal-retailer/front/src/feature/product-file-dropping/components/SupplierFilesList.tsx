import React from 'react';
import {SupplierFileRow} from '../hooks';
import {Badge, DownloadIcon, IconButton, Pagination, Table} from 'akeneo-design-system';
import {useDateFormatter, useTranslate, useRouter} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierFilesList} from './EmptySupplierFilesList';

export const SUPPLIER_FILES_PER_PAGE = 25;

type Props = {
    supplierFiles: SupplierFileRow[];
    totalSupplierFiles: number;
    currentPage: number;
    onChangePage: (pageNumber: number) => void;
};

const SupplierFilesList = ({supplierFiles, totalSupplierFiles, currentPage, onChangePage}: Props) => {
    const translate = useTranslate();
    const dateFormatter = useDateFormatter();
    const router = useRouter();

    return (
        <>
            {0 === supplierFiles.length && <EmptySupplierFilesList />}
            {0 < supplierFiles.length && (
                <>
                    <Pagination
                        followPage={onChangePage}
                        currentPage={currentPage}
                        totalItems={totalSupplierFiles}
                        itemsPerPage={SUPPLIER_FILES_PER_PAGE}
                    />

                    <Table>
                        <Table.Header>
                            <Table.HeaderCell>
                                {translate('supplier_portal.product_file_dropping.supplier_files.columns.upload_date')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('supplier_portal.product_file_dropping.supplier_files.columns.contributor')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('supplier_portal.product_file_dropping.supplier_files.columns.supplier')}
                            </Table.HeaderCell>
                            <Table.HeaderCell>
                                {translate('supplier_portal.product_file_dropping.supplier_files.columns.status')}
                            </Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                        </Table.Header>
                        <Table.Body>
                            {supplierFiles.map((supplierFile: SupplierFileRow) => {
                                const uploadedDate = dateFormatter(supplierFile.uploadedAt, {
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                });
                                return (
                                    <Table.Row key={supplierFile.identifier} onClick={() => {}}>
                                        <Table.Cell>{uploadedDate}</Table.Cell>
                                        <Table.Cell>{supplierFile.contributor}</Table.Cell>
                                        <Table.Cell>{supplierFile.supplier}</Table.Cell>
                                        <Table.Cell>
                                            {'Downloaded' === supplierFile.status && (
                                                <Badge level="primary">
                                                    {translate(
                                                        'supplier_portal.product_file_dropping.supplier_files.status.downloaded'
                                                    )}
                                                </Badge>
                                            )}
                                            {'To download' === supplierFile.status && (
                                                <Badge level="warning">
                                                    {translate(
                                                        'supplier_portal.product_file_dropping.supplier_files.status.to_download'
                                                    )}
                                                </Badge>
                                            )}
                                        </Table.Cell>
                                        <DownloadCell>
                                            <IconButton
                                                icon={<DownloadIcon />}
                                                title=""
                                                ghost={'borderless'}
                                                href={router.generate('supplier_portal_retailer_download_file', {
                                                    identifier: supplierFile.identifier,
                                                })}
                                            />
                                        </DownloadCell>
                                    </Table.Row>
                                );
                            })}
                        </Table.Body>
                    </Table>
                </>
            )}
        </>
    );
};

const DownloadCell = styled(Table.ActionCell)`
    width: 50px;
`;

export {SupplierFilesList};
