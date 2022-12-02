import React, {useState} from 'react';
import styled from 'styled-components';
import {Menu} from '../../components';
import {EmptyProductFileHistory, ProductFileList} from './components';
import {useProductFiles} from './hooks/useProductFiles';
import {useDebounce} from 'akeneo-design-system';

const ProductFileHistory = () => {
    const [page, setPage] = useState<number>(0);
    const [searchValue, setSearchValue] = useState<string>('');
    const debouncedSearch = useDebounce(searchValue);
    const productFiles = useProductFiles(page, debouncedSearch);

    if (!productFiles) {
        return null;
    }

    return (
        <Container>
            <Menu activeItem="history" />
            <Content>
                {0 === productFiles.totalNumberOfProductFiles && '' === debouncedSearch && <EmptyProductFileHistory />}
                {(0 < productFiles.totalNumberOfProductFiles || '' !== debouncedSearch) && (
                    <ProductFileList
                        productFiles={productFiles.product_files}
                        totalSearchResults={productFiles.totalSearchResults}
                        currentPage={page}
                        onChangePage={setPage}
                        searchValue={searchValue}
                        onChangeSearch={setSearchValue}
                    />
                )}
            </Content>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
`;

export {ProductFileHistory};
