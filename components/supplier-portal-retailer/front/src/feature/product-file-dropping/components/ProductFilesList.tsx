import React from 'react';
import {DownloadIcon, getColor, IconButton, Pagination, Table} from 'akeneo-design-system';
import {useDateFormatter, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {EmptyProductFilesList} from './EmptyProductFilesList';
import {ProductFileRow} from '../models/ProductFileRow';
import {useHistory} from 'react-router';

export const PRODUCT_FILES_PER_PAGE = 25;

type Props = {
    productFiles: ProductFileRow[];
    totalProductFiles: number;
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

const ProductFilesList = ({
    productFiles,
    totalProductFiles,
    currentPage,
    onChangePage,
    displaySupplierColumn = true,
}: Props) => {
    const translate = useTranslate();
    const history = useHistory();
    const dateFormatter = useDateFormatter();
    const router = useRouter();

    const goToProductFile = (productFileIdentifier: string) => {
        history.push(`/product-file/${productFileIdentifier}`);
    };

    return (
        <>
            {0 === productFiles.length && <EmptyProductFilesList />}
            {0 < productFiles.length && (
                <>
                    <Pagination
                        followPage={onChangePage}
                        currentPage={currentPage}
                        totalItems={totalProductFiles}
                        itemsPerPage={PRODUCT_FILES_PER_PAGE}
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
                            <Table.HeaderCell></Table.HeaderCell>
                        </Table.Header>
                        <Table.Body>
                            {productFiles.map((productFile: ProductFileRow) => {
                                const uploadedDate = dateFormatter(productFile.uploadedAt, {
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                });
                                return (
                                    <Table.Row
                                        key={productFile.identifier}
                                        onClick={() => goToProductFile(productFile.identifier)}
                                    >
                                        <Table.Cell>{uploadedDate}</Table.Cell>
                                        <Table.Cell>{productFile.contributor}</Table.Cell>
                                        {displaySupplierColumn && (
                                            <Table.Cell>
                                                {productFile.hasOwnProperty('supplier') && productFile.supplier}
                                            </Table.Cell>
                                        )}
                                        <DownloadCell>
                                            <StyledIconButton
                                                data-testid="Download icon"
                                                icon={<StyledDownloadIcon animateOnHover={true} />}
                                                title={translate(
                                                    'supplier_portal.product_file_dropping.supplier_files.columns.download'
                                                )}
                                                ghost={'borderless'}
                                                onClick={(event: any) => event.stopPropagation()}
                                                href={router.generate('supplier_portal_retailer_download_file', {
                                                    productFileIdentifier: productFile.identifier,
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

export {ProductFilesList};
