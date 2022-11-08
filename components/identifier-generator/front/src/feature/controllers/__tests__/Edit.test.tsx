import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {Edit} from '../';
import {IdentifierGenerator} from '../../models';

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
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 404,
      json: () => Promise.resolve({}),
    } as Response);

    render(<Edit />);
    expect(await screen.findByText('pim_error.404')).toBeInTheDocument();
    expect(screen.getByText('pim_error.identifier_generator_not_found')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  it('should render a generic error', async () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      status: 500,
      statusText: 'Fail',
      json: () => Promise.resolve({}),
    } as Response);

    render(<Edit />);
    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
    expect(screen.getByText('Fail')).toBeInTheDocument();
    mockedConsole.mockRestore();
  });

  it('should render the edit page', async () => {
    const initialGenerator: IdentifierGenerator = {
      code: 'initialCode',
      labels: {
        en_US: 'Initial Label',
      },
      conditions: [],
      structure: [],
      delimiter: null,
      target: 'sku',
    };

    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(initialGenerator),
    } as Response);

    render(<Edit />);
    expect(await screen.findByText('CreateOrEditGeneratorPage')).toBeInTheDocument();
    expect(screen.getByText(JSON.stringify(initialGenerator))).toBeInTheDocument();
  });
});
