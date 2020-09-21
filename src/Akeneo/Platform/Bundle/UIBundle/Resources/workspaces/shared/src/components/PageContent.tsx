import styled from 'styled-components';

const PageContent = styled.div`
  padding: 0 40px;
  height: calc(100vh - 130px);
  overflow: auto;
`;

export {PageContent};
/*
import React, {PropsWithChildren} from 'react';

export const PageContent = ({children}: PropsWithChildren<{}>) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-contentWithBottom'>
            <div className='AknDefault-mainContent'>{children}</div>
        </div>
    </div>
);

*/