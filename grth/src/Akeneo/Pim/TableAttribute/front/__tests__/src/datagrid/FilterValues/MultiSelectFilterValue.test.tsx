import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import MultiSelectFilterValue from "../../../../src/datagrid/FilterValues/MultiSelectFilterValue";
import {getComplexTableAttribute} from "../../../factories";

jest.mock('../../../../src/fetchers/SelectOptionsFetcher');

describe('MultiSelectFilterValue', () => {
  it('should display current value', async () => {
    renderWithProviders(<MultiSelectFilterValue
      value={['F', 'B']}
      onChange={jest.fn()}
      columnCode={'nutrition_score'}
      attribute={getComplexTableAttribute()}
    />);

    expect(await screen.findByText('F')).toBeInTheDocument();
    expect(screen.getByText('B')).toBeInTheDocument();
  });
});
