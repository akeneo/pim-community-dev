import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {MultiChannelInput} from './MultiChannelInput';
import userEvent from '@testing-library/user-event';

describe('MultiChannelInput', () => {
  it('renders the multi channel input', async () => {
    const handleChannelChange = jest.fn();
    await renderWithProviders(<MultiChannelInput onChange={handleChannelChange} />);

    expect(
      await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_channels')
    ).toBeInTheDocument();
  });

  it('can change the channel', async () => {
    const handleChannelChange = jest.fn();
    await renderWithProviders(<MultiChannelInput onChange={handleChannelChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('[ecommerce]'));
    userEvent.click(await screen.findByText('[mobile]'));

    expect(handleChannelChange).toHaveBeenCalledWith(['ecommerce', 'mobile']);
  });

  it('can reset channel options', async () => {
    const handleChannelChange = jest.fn();
    await renderWithProviders(<MultiChannelInput onChange={handleChannelChange} />);

    userEvent.click(screen.getByTitle('pim_common.open'));
    userEvent.click(await screen.findByText('[ecommerce]'));
    userEvent.click(await screen.findByText('[mobile]'));
    userEvent.click(await screen.findByText('akeneo.performance_analytics.control_panel.multi_input.all_channels'));

    expect(handleChannelChange).toHaveBeenCalledWith(['<all_channels>']);
  });
});
