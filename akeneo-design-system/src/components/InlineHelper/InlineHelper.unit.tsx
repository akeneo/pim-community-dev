import React from 'react';
import {render} from '../../storybook/test-util';
import {InlineHelper} from './InlineHelper';

describe('An inline helper', () => {
  it('renders information helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<InlineHelper level="info">{helperMessage}</InlineHelper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders warning helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<InlineHelper level="warning">{helperMessage}</InlineHelper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders error helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<InlineHelper level="error">{helperMessage}</InlineHelper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
