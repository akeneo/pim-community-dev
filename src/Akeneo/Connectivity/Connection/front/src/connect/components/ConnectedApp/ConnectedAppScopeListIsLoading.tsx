import React, {FC} from 'react';
import styled, {keyframes} from 'styled-components';

const loadingBreath = keyframes`
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
`;

const ScopeListContainer = styled.div`
    margin: 10px 20px;
`;

const SkeletonScopeListItem = styled.div`
    height: 24px;
    animation: ${loadingBreath} 2s infinite;
    content: '';
    top: 0px;
    left: 0px;
    width: 100%;
    background: linear-gradient(270deg, #fdfdfd, #eee);
    background-size: 400% 400%;
    border-radius: 5px;
    margin-bottom: 13px;
`;

export const ConnectedAppScopeListIsLoading: FC = () => {
    return (
        <ScopeListContainer>
            <SkeletonScopeListItem />
            <SkeletonScopeListItem />
            <SkeletonScopeListItem />
        </ScopeListContainer>
    );
};
