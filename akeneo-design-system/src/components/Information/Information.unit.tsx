import React from 'react';
import {render} from '../../storybook/test-util';
import {HighlightTitle, Information} from './Information';
import {UsersIllustration} from '../../illustrations';

describe('A helper', () => {
  it('it renders an information helper', () => {
    const helperMessage = 'A Helper message';
    const helperTitle = (
      <>
        <HighlightTitle>Highlight</HighlightTitle>
        helper title
      </>
    );

    const {getByText} = render(
      <Information title={helperTitle} illustration={UsersIllustration}>
        {helperMessage}
      </Information>
    );

    expect(getByText('Highlight')).toBeInTheDocument();
    expect(getByText('helper title')).toBeInTheDocument();
    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
