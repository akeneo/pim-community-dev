import styled from 'styled-components';

export const CriterionFields = styled.div`
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    flex-grow: 1;
`;

export const CriterionField = styled.div<{width?: number}>`
    flex-basis: ${({width = 160}) => `${width}px`};
    flex-shrink: 0;
    width: ${({width = 160}) => `${width}px`};
`;
