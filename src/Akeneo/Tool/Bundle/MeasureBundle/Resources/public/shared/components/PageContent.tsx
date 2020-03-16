import React, {PropsWithChildren} from 'react';

const PageContent = ({children}: PropsWithChildren<{}>) => (
  <div className="AknDefault-contentWithColumn">
    <div className="AknDefault-contentWithBottom">
      <div className="AknDefault-mainContent">{children}</div>
    </div>
  </div>
);

export {PageContent};
