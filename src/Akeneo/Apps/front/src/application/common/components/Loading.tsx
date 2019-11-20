import styled from 'styled-components';
import loadingSpinnerUrl from '../assets/loading-spinner.svg';

export const Loading = styled.div`
    background: transparent url(${loadingSpinnerUrl}) no-repeat center;
    flex: 1;
    align-self: stretch;
    display: flex;
    background-size: 60px;
    min-height: 60px;
`;
