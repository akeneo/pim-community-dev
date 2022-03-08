import React from 'react';
import {CreateSupplier} from './CreateSupplier';
import {CityIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const Container = styled.div`
    display: flex;
    align-items: center;
    flex-direction: column;
`;

type EmptySupplierProps = {
    onSupplierCreated: () => void;
};

const EmptySupplierList = ({onSupplierCreated}: EmptySupplierProps) => {
    const translate = useTranslate();

    return (
        <Container>
            <CityIllustration size={256} />
            <p>{translate('onboarder.supplier.no_supplier')}</p>
            <CreateSupplier
                onSupplierCreated={onSupplierCreated}
                createButtonlabel={translate('onboarder.supplier.create_supplier.create_button.label')}
            />
        </Container>
    );
};

export {EmptySupplierList};
