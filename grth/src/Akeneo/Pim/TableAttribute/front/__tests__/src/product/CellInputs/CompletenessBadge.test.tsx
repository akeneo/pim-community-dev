import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {CompletenessBadge} from '../../../../src/product/CellInputs/CompletenessBadge';
import {screen} from '@testing-library/react';
import {theme} from '@akeneo-pim-community/connectivity-connection/src/common/styled-with-theme';

describe('CompletenessBadge', () => {
  it('should render component with full completeness', () => {
    const color = theme.color[`${theme.palette['primary']}${140}`];
    renderWithProviders(<CompletenessBadge completeness={{complete: 2, required: 2}} />);

    expect(screen.getByText('100%')).toBeInTheDocument();
    expect(screen.getByText('100%')).toHaveStyle({color: color});
  });

  it('should render 0 when there are no required completeness', () => {
    const color = theme.color[`${theme.palette['danger']}${140}`];
    renderWithProviders(<CompletenessBadge completeness={{complete: 0, required: 0}} />);

    expect(screen.getByText('0%')).toBeInTheDocument();
    expect(screen.getByText('0%')).toHaveStyle({color: color});
  });

  it('should render component with medium level of completeness', () => {
    const color = theme.color[`${theme.palette['warning']}${140}`];
    renderWithProviders(<CompletenessBadge completeness={{complete: 1, required: 2}} />);

    expect(screen.getByText('50%')).toBeInTheDocument();
    expect(screen.getByText('50%')).toHaveStyle({color: color});
  });

  it('should not render anything when there are no completeness as props', () => {
    renderWithProviders(<CompletenessBadge />);

    expect(screen.queryByText('%')).not.toBeInTheDocument();
  });
});
