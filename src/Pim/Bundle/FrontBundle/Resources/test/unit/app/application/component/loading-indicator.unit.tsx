import LoadingIndicator from 'pimfront/app/application/component/loading-indicator';
import * as React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- loading indicator', () => {
  test('Display the loading indicator not loading', () => {
    const indicator = mount(<LoadingIndicator loading={false} />);
    expect(indicator.find('.AknLoadingIndicator').length).toEqual(1);
    expect(indicator.find('.AknLoadingIndicator--loading').length).toEqual(0);
  });

  test('Display the loading indicator loading', () => {
    const indicator = mount(<LoadingIndicator loading={true} />);
    expect(indicator.find('.AknLoadingIndicator').length).toEqual(1);
    expect(indicator.find('.AknLoadingIndicator--loading').length).toEqual(1);
  });
});
