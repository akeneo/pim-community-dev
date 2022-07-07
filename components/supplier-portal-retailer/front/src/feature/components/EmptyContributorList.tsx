import React from 'react';
import {CityIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const EmptyContributorList = () => {
    const translate = useTranslate();
    return (
        <Container>
            <CityIllustration size={256} />
            <div>{translate('onboarder.supplier.supplier_edit.contributors_form.no_contributor')}</div>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

export {EmptyContributorList};
