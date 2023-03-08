import React from 'react';
import {render, screen, waitFor, act, fireEvent} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {Router} from 'react-router-dom';
import {createMemoryHistory} from 'history';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {QueryClient, QueryClientProvider} from 'react-query';

jest.mock('../CreateOrEditGeneratorPage');

describe('CreateGeneratorPage', () => {
  it('should create a generator', async () => {
    const history = createMemoryHistory();
    const queryClient = new QueryClient();
    const mockedQueryClient = jest.spyOn(queryClient, 'invalidateQueries');
    render(
      <QueryClientProvider client={queryClient}>
        <Router history={history}>
          <CreateGeneratorPage initialGenerator={initialGenerator} />
        </Router>
      </QueryClientProvider>
    );
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    await waitFor(() => expect(history.length).toBeGreaterThan(1));
    expect(history.location.pathname).toBe('/initialCode');
    expect(mockedQueryClient).toBeCalledWith('getIdentifierGenerator');
  });

  it('should display validation errors', async () => {
    render(<CreateGeneratorPage initialGenerator={{...initialGenerator, code: 'validation-error'}} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(await screen.findByText('a path a message')).toBeInTheDocument();
    expect(await screen.findByText('another message')).toBeInTheDocument();
  });

  it('should manage default errors', () => {
    render(<CreateGeneratorPage initialGenerator={{...initialGenerator, code: 'back-error'}} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });
  });

  it('should check generator validation on save', () => {
    render(<CreateGeneratorPage initialGenerator={{...initialGenerator, structure: []}} />);
    expect(screen.getByText('CreateOrEditGeneratorPage')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByText('Main button'));
    });

    expect(screen.getByText('structure The structure must contain at least one property')).toBeInTheDocument();
  });
});
