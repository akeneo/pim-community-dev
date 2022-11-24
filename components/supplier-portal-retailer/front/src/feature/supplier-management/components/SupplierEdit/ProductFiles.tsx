import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {ProductFilesList} from '../../../product-file-dropping/components';
import {useProductFiles} from '../../hooks';

type Props = {
    supplierIdentifier: string;
};

const ProductFiles = ({supplierIdentifier}: Props) => {
    const [page, setPage] = useState<number>(1);
    const [productFiles, totalProductFiles] = useProductFiles(supplierIdentifier, page);

    useEffect(() => {
        0 < totalProductFiles && setPage(1);
    }, [totalProductFiles]);

    return (
        <Container>
            <ProductFilesList
                productFiles={productFiles}
                totalProductFiles={totalProductFiles}
                currentPage={page}
                onChangePage={setPage}
                displaySupplierColumn={false}
            />
        </Container>
    );
};

const Container = styled.div`
    margin-top: 10px;
`;

export {ProductFiles};
