import React from 'react';
import {Badge, DownloadIcon, IconButton, Pagination, Table, getColor} from 'akeneo-design-system';
import {useDateFormatter, useTranslate, useRouter} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierFilesList} from './EmptySupplierFilesList';
import {SupplierFileRow} from '../models/SupplierFileRow';

export const SUPPLIER_FILES_PER_PAGE = 25;

type Props = {
    supplierFiles: SupplierFileRow[];
    totalSupplierFiles: number;
    currentPage: number;
    onChangePage: (pageNumber: number) => void;
    displaySupplierColumn?: boolean;
};

const StyledDownloadIcon = styled(DownloadIcon)`
    color: ${getColor('grey100')};
`;

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const SupplierFilesList = ({
    supplierFiles,
    totalSupplierFiles,
    currentPage,
    onChangePage,
    displaySupplierColumn = true,
}: Props) => {
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
                            {displaySupplierColumn && (
                                <Table.HeaderCell>
                                    {translate('supplier_portal.product_file_dropping.supplier_files.columns.supplier')}
                                </Table.HeaderCell>
                            )}
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
                                        {displaySupplierColumn && (
                                            <Table.Cell>
                                                {supplierFile.hasOwnProperty('supplier') ? supplierFile.supplier : null}
                                            </Table.Cell>
                                        )}
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
                                            <StyledIconButton
                                                data-testid="Download icon"
                                                icon={<StyledDownloadIcon animateOnHover={true} />}
                                                title={translate(
                                                    'supplier_portal.product_file_dropping.supplier_files.columns.download'
                                                )}
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
