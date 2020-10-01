import React from 'react';
import {render} from '../../storybook/test-util';
import {InformationHelper} from './InformationHelper';
import {UsersIllustration} from '../../illustrations';

describe('A helper', () => {
  it('renders an information helper', () => {
    const helperTitle = 'Helper title';
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <InformationHelper title={helperTitle} illustration={UsersIllustration}>
        {helperMessage}
      </InformationHelper>
    );

    expect(getByText(helperTitle)).toBeInTheDocument();
    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
