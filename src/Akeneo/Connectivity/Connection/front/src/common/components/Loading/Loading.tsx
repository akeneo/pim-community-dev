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
    display: grid;
`;

const Spinner = styled(LoadingSpinner)`
    background-size: 60px;
    min-height: 60px;
    background: center;
    margin: auto;
`;

export {Loading};
