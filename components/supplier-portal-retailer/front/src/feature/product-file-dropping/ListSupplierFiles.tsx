import React, {useEffect, useState} from 'react';
import {Badge, Breadcrumb, DownloadIcon, getColor, Pagination, pimTheme, Table} from 'akeneo-design-system';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptySupplierFilesList} from './components';
import {SupplierFileRow, useSupplierFiles} from './hooks';

export const SUPPLIER_FILES_PER_PAGE = 25;

const ListSupplierFiles = () => {
    const translate = useTranslate();
    const [page, setPage] = useState<number>(1);
    const [supplierFiles, totalSupplierFiles] = useSupplierFiles(page);

    useEffect(() => {
        0 < totalSupplierFiles && setPage(1);
    }, [totalSupplierFiles]);

    return (
        <>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.root')}
                        </Breadcrumb.Step>
                        <Breadcrumb.Step>
                            {translate('supplier_portal.product_file_dropping.breadcrumb.product_files')}
                        </Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Title>
                    {translate(
                        'supplier_portal.product_file_dropping.supplier_files.title',
                        {count: totalSupplierFiles},
                        totalSupplierFiles
                    )}
                </PageHeader.Title>
            </PageHeader>
            <StyledPageContent>
                {0 === supplierFiles.length && <EmptySupplierFilesList />}
                {0 < supplierFiles.length && (
                    <>
                        <Pagination
                            followPage={setPage}
                            currentPage={page}
                            totalItems={totalSupplierFiles}
                            itemsPerPage={SUPPLIER_FILES_PER_PAGE}
                        />

                        <Table>
                            <Table.Header>
                                <Table.HeaderCell>
                                    {translate(
                                        'supplier_portal.product_file_dropping.supplier_files.columns.upload_date'
                                    )}
                                </Table.HeaderCell>
                                <Table.HeaderCell>
                                    {translate(
                                        'supplier_portal.product_file_dropping.supplier_files.columns.contributor'
                                    )}
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
                                {supplierFiles.map((supplierFile: SupplierFileRow) => (
                                    <Table.Row key={supplierFile.identifier}>
                                        <Table.Cell>{supplierFile.uploadedAt}</Table.Cell>
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
                                        <Table.ActionCell>
                                            <StyledDownloadIcon color={pimTheme.color.grey100} />
                                        </Table.ActionCell>
                                    </Table.Row>
                                ))}
                            </Table.Body>
                        </Table>
                    </>
                )}
            </StyledPageContent>
        </>
    );
};

const StyledPageContent = styled(PageContent)`
    display: flex;
    flex-direction: column;
`;

const StyledDownloadIcon = styled(DownloadIcon)`
    cursor: pointer;
    color: ${getColor('grey100')};
`;

export {ListSupplierFiles};
