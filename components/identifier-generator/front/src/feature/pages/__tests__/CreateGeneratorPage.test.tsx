import React from 'react';
import {mockResponse, render, screen} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {IdentifierGenerator} from '../../models';
import {Router} from 'react-router-dom';
import {act, fireEvent, waitFor} from '@testing-library/react';
import {createMemoryHistory} from 'history';

jest.mock('../CreateOrEditGeneratorPage');

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

describe('CreateGeneratorPage', () => {
  it('should create a generator', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      status: 201,
      body: initialGenerator,
      json: initialGenerator,
    });

    const history = createMemoryHistory();
    render(
      <Router history={history}>
        <CreateGeneratorPage initialGenerator={initialGenerator} />
      </Router>
    );
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => history.length > 1);
    expectCall();
    expect(history.location.pathname).toBe('/initialCode');
  });

  it('should display validation errors', async () => {
    const violationErrors = [{message: 'a message', path: 'a path'}, {message: 'another message'}];

    mockResponse('akeneo_identifier_generator_rest_create', 'POST', {json: violationErrors, status: 400});

    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(await screen.findByText('a path a message')).toBeInTheDocument();
    expect(await screen.findByText('another message')).toBeInTheDocument();
  });

  it('should manage default errors', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_create', 'POST', {
      ok: false,
      status: 500,
      body: initialGenerator,
    });

    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => {
      expectCall();
    });
  });
});
