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
describe('Information supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Information illustration={UsersIllustration} title="Some information" ref={ref}>
      Some Information
    </Information>
  );
  expect(ref.current).not.toBe(null);
});

describe('Information supports ...rest props', () => {
  const {container} = render(
    <Information illustration={UsersIllustration} title="Some information" data-my-attribute="my_value">
      Some Information
    </Information>
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
