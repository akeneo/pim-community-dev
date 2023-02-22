import React from 'react';
import {mockResponse, render, screen} from '../../tests/test-utils';
import {Edit} from '../';
import initialGenerator from '../../tests/fixtures/initialGenerator';

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
    mockResponse('akeneo_identifier_generator_rest_get', 'GET', {status: 404, ok: false});

    render(<Edit />);
    expect(await screen.findByText('pim_error.404')).toBeInTheDocument();
    expect(screen.getByText('pim_error.identifier_generator_not_found')).toBeInTheDocument();
  });

  it('should render a generic error', async () => {
    mockResponse('akeneo_identifier_generator_rest_get', 'GET', {status: 500, ok: false, statusText: 'Fail'});

    render(<Edit />);
    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });

  it('should render the edit page', async () => {
    mockResponse('akeneo_identifier_generator_rest_get', 'GET', {json: initialGenerator});

    render(<Edit />);
    expect(await screen.findByText('CreateOrEditGeneratorPage')).toBeInTheDocument();
    expect(screen.getByText(JSON.stringify(initialGenerator))).toBeInTheDocument();
  });
});
