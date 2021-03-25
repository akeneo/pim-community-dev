import React, {PropsWithChildren} from 'react';
import styled from 'styled-components';

const MainContent = styled.div`
    min-height: calc(100vh - 126px);
`;

export const PageContent = ({children}: PropsWithChildren<{}>) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-contentWithBottom'>
            <MainContent className='AknDefault-mainContent'>{children}</MainContent>
        </div>
    </div>
);
