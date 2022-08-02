import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {SupplierFilesList} from '../../../product-file-dropping/components/SupplierFilesList';
import {useSupplierFiles} from '../../hooks';

type Props = {
    supplierIdentifier: string;
};

const ProductFiles = ({supplierIdentifier}: Props) => {
    const [page, setPage] = useState<number>(1);
    const [supplierFiles, totalSupplierFiles] = useSupplierFiles(supplierIdentifier, page);

    useEffect(() => {
        0 < totalSupplierFiles && setPage(1);
    }, [totalSupplierFiles]);

    return (
        <Container>
            <SupplierFilesList
                supplierFiles={supplierFiles}
                totalSupplierFiles={totalSupplierFiles}
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
