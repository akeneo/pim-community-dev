import React, {useState} from 'react';
import styled from 'styled-components';
import {Menu} from '../../components';
import {EmptyProductFileHistory, ProductFileList} from './components';
import {useProductFiles} from './hooks/useProductFiles';

const ProductFileHistory = () => {
    const [page, setPage] = useState<number>(1);
    const productFiles = useProductFiles(page);

    if (!productFiles) {
        return null;
    }

    return (
        <Container>
            <Menu activeItem="history" />
            <Content>
                {0 === productFiles.total && <EmptyProductFileHistory />}
                {0 < productFiles.total && (
                    <ProductFileList
                        productFiles={productFiles.product_files}
                        totalProductFiles={productFiles.total}
                        currentPage={page}
                        onChangePage={setPage}
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
