import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {MultiLocaleInput} from './MultiLocaleInput';
import userEvent from '@testing-library/user-event';

describe('MultiLocaleInput', () => {
  it('renders the multi locale input', async () => {
    const handleLocaleChange = jest.fn();
    await renderWithProviders(<MultiLocaleInput onChange={handleLocaleChange} />);

    expect(
      await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_locales')
    ).toBeInTheDocument();
  });

  it('can change the locale', async () => {
    const handleLocaleChange = jest.fn();
    await renderWithProviders(<MultiLocaleInput onChange={handleLocaleChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('French'));
    userEvent.click(await screen.findByText('English'));
    expect(handleLocaleChange).toHaveBeenCalledWith(['fr_FR', 'en_US']);
  });

  it('can reset locale options', async () => {
    const handleLocaleChange = jest.fn();
    await renderWithProviders(<MultiLocaleInput onChange={handleLocaleChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('French'));
    userEvent.click(await screen.findByText('English'));
    userEvent.click(await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_locales'));

    expect(handleLocaleChange).toHaveBeenCalledWith(['<all_locales>']);
  });
});
