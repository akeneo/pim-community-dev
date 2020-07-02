import React from 'react';
import styled from 'styled-components';
import {LoadingSpinner} from './LoadingSpinner';

const Loading = () => {
    return (
        <Container>
            <Spinner />
        </Container>
    );
};

const Container = styled.div`
    height: 100%;
    display: flex;
`;

const Spinner = styled(LoadingSpinner)`
    min-height: 60px;
    margin: auto;
`;

export {Loading};
