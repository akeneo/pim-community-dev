import React from 'react';
import {CreateSupplier} from './CreateSupplier';
import {CityIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const Container = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

type EmptySupplierProps = {
    onSupplierCreated: () => void;
};

const EmptySupplierList = ({onSupplierCreated}: EmptySupplierProps) => {
    const translate = useTranslate();

    return (
        <Container>
            <CityIllustration size={256} />
            <NoSupplierText>{translate('onboarder.supplier.supplier_list.no_supplier')}</NoSupplierText>
            <CreateSupplier
                onSupplierCreated={onSupplierCreated}
                createButtonlabel={translate('onboarder.supplier.supplier_create.create_button.label')}
            />
        </Container>
    );
};

const NoSupplierText = styled.div`
    margin-bottom: 30px;
`;

export {EmptySupplierList};
