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
    const [productFiles, totalProductFiles] = useProductFiles(supplierIdentifier, page, debouncedSearchValue);

    useEffect(() => {
        0 < totalProductFiles && setPage(1);
    }, [totalProductFiles]);

    return (
        <Container>
            {0 === totalProductFiles && '' === searchValue ? (
                <EmptyProductFilesList message="supplier_portal.product_file_dropping.supplier_files.no_files" />
            ) : (
                <ProductFilesList
                    productFiles={productFiles}
                    totalSearchResults={totalProductFiles}
                    currentPage={page}
                    onChangePage={setPage}
                    searchValue={searchValue}
                    onSearch={setSearchValue}
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
