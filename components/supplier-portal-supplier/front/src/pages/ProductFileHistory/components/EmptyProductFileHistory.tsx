import React from 'react';
import {FormattedMessage} from 'react-intl';
import styled from 'styled-components';

const Container = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

const EmptyProductFileHistory = () => {
    // @todo To complete once the mockup provided
    return (
        <Container>
            <FormattedMessage defaultMessage="There is no product files yet." id="Gs2nav" />
        </Container>
    );
};

export {EmptyProductFileHistory};
