import React from 'react';
import {renderWithProviders} from '../../tests';
import {fireEvent, screen} from '@testing-library/react';
import {MultiFamilyInput} from './MultiFamilyInput';
import userEvent from '@testing-library/user-event';

describe('MultiFamilyInput', () => {
  it('renders the multi family input', async () => {
    const handleFamilyChange = jest.fn();
    await renderWithProviders(<MultiFamilyInput onChange={handleFamilyChange} />);

    expect(
      await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_families')
    ).toBeInTheDocument();
  });

  it('can change the family', async () => {
    const handleFamilyChange = jest.fn();
    await renderWithProviders(<MultiFamilyInput onChange={handleFamilyChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('[family_10]'));
    userEvent.click(await screen.findByText('[family_12]'));
    expect(handleFamilyChange).toHaveBeenCalledWith(['family_10', 'family_12']);
  });

  it('can reset family options', async () => {
    const handleFamilyChange = jest.fn();
    await renderWithProviders(<MultiFamilyInput onChange={handleFamilyChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('[family_10]'));
    userEvent.click(await screen.findByText('[family_12]'));
    userEvent.click(await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_families'));

    expect(handleFamilyChange).toHaveBeenCalledWith(['<all_families>']);
  });

  it('handles search on family input', async () => {
    const handleFamilyChange = jest.fn();
    await renderWithProviders(<MultiFamilyInput onChange={handleFamilyChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('[family_1]')).toBeInTheDocument();

    const input = screen.getByRole('textbox');
    fireEvent.click(input);
    fireEvent.change(input, {target: {value: '5'}});

    expect(await screen.findByText('[family_5]')).toBeInTheDocument();
    expect(await screen.findByText('[family_50]')).toBeInTheDocument();
    expect(screen.queryByText('[family_1]')).not.toBeInTheDocument();
  });
});
