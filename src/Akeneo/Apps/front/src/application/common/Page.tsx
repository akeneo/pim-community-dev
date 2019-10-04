import * as React from 'react';

export const Page = ({children}: React.PropsWithChildren<{}>) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-thirdColumnContainer'>
            <div className='AknDefault-thirdColumn' />
        </div>

        <div className='AknDefault-contentWithBottom'>
            <div className='AknDefault-mainContent'>{children}</div>
        </div>
    </div>
);
