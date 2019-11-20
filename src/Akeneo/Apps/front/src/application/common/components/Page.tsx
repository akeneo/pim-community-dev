import React, {PropsWithChildren} from 'react';
import styled from 'styled-components';

export const Page = ({children}: PropsWithChildren<{}>) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-thirdColumnContainer'>
            <div className='AknDefault-thirdColumn' />
        </div>

        <div className='AknDefault-contentWithBottom'>
            <Content className='AknDefault-mainContent'>{children}</Content>
        </div>
    </div>
);

const Content = styled.div`
    display: flex;
    flex-direction: column;
`;
