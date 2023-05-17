import React from 'react';
import {render, screen, waitFor} from '../../tests/test-utils';
import {EditGeneratorPage} from '../';
import userEvent from '@testing-library/user-event';
import {NotificationLevel} from '@akeneo-pim-community/shared';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {act, fireEvent} from '@testing-library/react';
import {server} from '../../mocks/server';
import {rest} from 'msw';

const mockNotify = jest.fn();

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useRouter: () => ({
    generate: (key: string) => key
  }),
  useNotify: () => mockNotify,
}));

describe('EditGeneratorPage', () => {
  it('should render page', () => {
    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });

  it('should save generator and show toast', async () => {
    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.save'));

    await waitFor(() => expect(mockNotify).toHaveBeenCalled());
    expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.SUCCESS, 'pim_identifier_generator.flash.update.success');
  });

  it('should save generator with error and show toast', async () => {
    server.use(
      rest.patch('/akeneo_identifier_generator_rest_update', (req, res, ctx) => {
        return res(ctx.status(500), ctx.json([
          {
            message: 'Association type code may contain only letters, numbers and underscores',
            path: 'code',
          },
        ]));
      })
    );

    render(<EditGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.save'));

    await waitFor(() => expect(mockNotify).toHaveBeenCalled());
    expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'pim_identifier_generator.flash.create.error');
  });

  it('should check generator validation on save', () => {
    render(<EditGeneratorPage initialGenerator={{...initialGenerator, structure: []}} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('pim_common.save'));
    });

    expect(mockNotify).not.toHaveBeenCalled();
    // we should see a red pill to know there is an error
    expect(screen.getByRole('alert')).toBeInTheDocument();
  });
});
