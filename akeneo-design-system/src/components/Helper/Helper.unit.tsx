import React from 'react';
import {render} from '../../storybook/test-util';
import {Helper} from './Helper';

describe('A helper', () => {
  it('it renders a info helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<Helper level="info">{helperMessage}</Helper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('it renders a warning helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<Helper level="warning">{helperMessage}</Helper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('it renders a error helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(<Helper level="error">{helperMessage}</Helper>);

    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
