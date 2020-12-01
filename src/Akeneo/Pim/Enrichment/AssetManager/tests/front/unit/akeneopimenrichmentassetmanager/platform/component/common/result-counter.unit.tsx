import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';

test('It displays no count', () => {
  const {container} = renderWithProviders(<ResultCounter />);

  expect(container).toBeEmptyDOMElement();
});

test('It displays a count', () => {
  renderWithProviders(<ResultCounter count={10} />);

  expect(screen.getByText('pim_asset_manager.result_counter')).toBeInTheDocument();
});
