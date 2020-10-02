import React from 'react';
import {render} from '../../storybook/test-util';
import {Information} from './Information';
import {UsersIllustration} from '../../illustrations';

describe('A helper', () => {
  it('renders an information helper', () => {
    const helperTitle = 'Helper title';
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Information title={helperTitle} illustration={UsersIllustration}>
        {helperMessage}
      </Information>
    );

    expect(getByText(helperTitle)).toBeInTheDocument();
    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
