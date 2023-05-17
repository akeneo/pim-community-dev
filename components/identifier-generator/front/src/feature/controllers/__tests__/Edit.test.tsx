import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {Edit} from '../';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {rest} from 'msw';
import {server} from '../../mocks/server';

jest.mock('../../pages/CreateOrEditGeneratorPage');

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: () => ({
    identifierGeneratorCode: 'my_generator',
  }),
}));

describe('Edit', () => {
  it('should display loading icon', () => {
    render(<Edit />);
    expect(screen.getByTestId('loadingIcon')).toBeInTheDocument();
  });

  it('should render a 404 on non existing generator', async () => {
    server.use(
      rest.get('/akeneo_identifier_generator_rest_get', (req, res, ctx) => {
        return res(ctx.status(404));
      })
    );

    render(<Edit />);
    expect(await screen.findByText('pim_error.404')).toBeInTheDocument();
    expect(screen.getByText('pim_error.identifier_generator_not_found')).toBeInTheDocument();
  });

  it('should render a generic error', async () => {
    server.use(
      rest.get('/akeneo_identifier_generator_rest_get', (req, res, ctx) => {
        return res(ctx.status(500));
      })
    );
    render(<Edit />);
    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });

  it('should render the edit page', async () => {
    render(<Edit />);
    expect(await screen.findByText('CreateOrEditGeneratorPage')).toBeInTheDocument();
    expect(screen.getByText(JSON.stringify(initialGenerator))).toBeInTheDocument();
  });
});
