import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {CompletenessBadge} from '../../../../src/product/CellInputs/CompletenessBadge';
import {screen} from '@testing-library/react';

describe('CompletenessBadge', () => {
  it('should render component with full completeness', () => {
    renderWithProviders(<CompletenessBadge completeness={{complete: 2, required: 2}} />);

    expect(screen.getByText('100%')).toBeInTheDocument();
    expect(screen.getByText('100%')).toHaveStyle({color: 'rgb(61, 107, 69)'});
  });

  it('should render 0 when there are no required completeness', () => {
    renderWithProviders(<CompletenessBadge completeness={{complete: 0, required: 0}} />);

    expect(screen.getByText('0%')).toBeInTheDocument();
    expect(screen.getByText('0%')).toHaveStyle({color: 'rgb(127, 57, 47)'});
  });

  it('should render component with medium level of completeness', () => {
    renderWithProviders(<CompletenessBadge completeness={{complete: 1, required: 2}} />);

    expect(screen.getByText('50%')).toBeInTheDocument();
    expect(screen.getByText('50%')).toHaveStyle({color: 'rgb(149, 108, 37)'});
  });

  it('should not render anything when there are no completeness as props', () => {
    renderWithProviders(<CompletenessBadge />);

    expect(screen.queryByText('%')).not.toBeInTheDocument();
  });
});
