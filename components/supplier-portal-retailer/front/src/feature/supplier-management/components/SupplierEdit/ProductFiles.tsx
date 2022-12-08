import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {useDebounce} from 'akeneo-design-system';
import {EmptyProductFilesList, ProductFilesList} from '../../../product-file-dropping/components';
import {useProductFiles} from '../../hooks';

type Props = {
    supplierIdentifier: string;
};

const ProductFiles = ({supplierIdentifier}: Props) => {
    const [page, setPage] = useState<number>(1);
    const [searchValue, setSearchValue] = useState('');
    const debouncedSearchValue = useDebounce(searchValue);
    const [importStatusValue, setImportStatusValue] = useState<null | string>(null);
    const [productFiles, totalSearchResults] = useProductFiles(
        supplierIdentifier,
        page,
        debouncedSearchValue,
        importStatusValue
    );

    useEffect(() => {
        0 < totalSearchResults && setPage(1);
    }, [totalSearchResults, searchValue, importStatusValue]);

    return (
        <Container>
            {0 === totalSearchResults && '' === searchValue && null === importStatusValue ? (
                <EmptyProductFilesList message="supplier_portal.product_file_dropping.supplier_files.no_files" />
            ) : (
                <ProductFilesList
                    productFiles={productFiles}
                    totalSearchResults={totalSearchResults}
                    currentPage={page}
                    onChangePage={setPage}
                    searchValue={searchValue}
                    onSearch={setSearchValue}
                    importStatusValue={importStatusValue}
                    handleImportStatusChange={setImportStatusValue}
                    displaySupplierColumn={false}
                />
            )}
        </Container>
    );
};

const Container = styled.div`
    margin-top: 10px;
`;

export {ProductFiles};
