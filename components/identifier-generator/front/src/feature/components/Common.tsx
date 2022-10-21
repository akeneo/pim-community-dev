import React from 'react';
import {Helper} from 'akeneo-design-system';

const CommonHelper: React.FC<{}> = () => {
  return (
    <Helper level="error">
      Under Construction: The Akeneo Product Team is hard at work developing new features for you. This feature will
      launch soon, but is currently under development. Please do not attempt to use this feature as it could lead to
      unexpected behaviors that impact your product data.
    </Helper>
  );
};

const Common = {
  Helper: CommonHelper,
};

export {Common};
