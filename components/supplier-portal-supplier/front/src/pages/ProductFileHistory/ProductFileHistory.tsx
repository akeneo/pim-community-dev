import React from 'react';
import styled from 'styled-components';
import {Menu} from '../../components';
import {EmptyProductFileHistory, ProductFileList} from './components';
import {useQuery} from 'react-query';
import {fetchProductFiles} from './api/fetchProductFiles';
import {ProductFile} from './model/ProductFile';

const ProductFileHistory = () => {
    const {data: productFiles} = useQuery<ProductFile[]>('fetchProductFiles', () => fetchProductFiles());

    if (!productFiles) {
        return null;
    }

    return (
        <Container>
            <Menu activeItem="history" />
            <Content>
                {0 === productFiles.length && <EmptyProductFileHistory />}
                {0 < productFiles.length && <ProductFileList productFiles={productFiles} />}
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
