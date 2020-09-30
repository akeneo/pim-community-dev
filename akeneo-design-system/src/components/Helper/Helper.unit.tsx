import React from 'react';
import {render} from '../../storybook/test-util';
import {HelperTitle, Helper} from './Helper';
import {UsersIllustration} from '../../illustrations';

describe('A helper', () => {
  it('renders a big helper', () => {
    const helperTitle = 'Helper title';
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper type="big" level="info">
        <HelperTitle>{helperTitle}</HelperTitle>
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperTitle)).toBeInTheDocument();
    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders a small helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper type="small" level="info">
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders an inline helper', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper type="inline" level="info">
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders an inline helper1', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper type="small" level="error">
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders an inline helper2', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper type="small" level="warning">
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperMessage)).toBeInTheDocument();
  });

  it('renders an inline helper2', () => {
    const helperMessage = 'A Helper message';

    const {getByText} = render(
      <Helper icon={UsersIllustration} type="inline" level="warning">
        {helperMessage}
      </Helper>
    );

    expect(getByText(helperMessage)).toBeInTheDocument();
  });
});
