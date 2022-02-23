import React from 'react';
import {CreateSupplier} from "./CreateSupplier";
import {CityIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from "styled-components";

const EmptySupplierList = () => {
    const translate = useTranslate();
    const Container = styled.div`
        display: flex;
        align-items: center;
        flex-direction: column;
    `;

    return (
        <Container>
            <CityIllustration size={256} />
            <p>
                {translate('onboarder.supplier.no_supplier')}
            </p>
            <CreateSupplier onSupplierCreated={() => {}}/>
        </Container>
    );
}

export {EmptySupplierList};

