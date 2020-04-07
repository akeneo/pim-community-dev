import React from 'react';

export const Content: React.FunctionComponent = ({ children }) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-contentWithBottom'>
            <div className='AknDefault-mainContent'>{children}</div>
        </div>
    </div>
);
