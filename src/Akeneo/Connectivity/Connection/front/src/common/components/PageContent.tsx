import React, {FC, PropsWithChildren} from 'react';

type Props = {
    pageHeaderHeight?: number;
};

export const PageContent: FC<PropsWithChildren<Props>> = ({pageHeaderHeight, children}) => {
    const headerHeight = pageHeaderHeight ?? 126;
    const style = {
        minHeight: `calc(100vh - ${headerHeight}px)`,
    };

    return (
        <div className='AknDefault-contentWithColumn'>
            <div className='AknDefault-contentWithBottom'>
                <div className='AknDefault-mainContent' style={style}>
                    {children}
                </div>
            </div>
        </div>
    );
};
