import * as React from 'react';

export default ({loading}: {loading: boolean}) => (
  <div className={`AknLoadingIndicator ${loading ? 'AknLoadingIndicator--loading' : ''}`} />
);
