import React from 'react';
import {ScopeAndLocaleSelector} from '../ScopeAndLocaleSelector';
import {render, waitFor} from '../../tests/test-utils';

describe('ScopeAndLocaleSelector', () => {
  it('should render error', async () => {
    const screen = render(
      <ScopeAndLocaleSelector attributeCode={'unknown_attribute'} onChange={jest.fn()} locale={null} scope={null} />
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.general')).toBeInTheDocument();
    });
  });
});
