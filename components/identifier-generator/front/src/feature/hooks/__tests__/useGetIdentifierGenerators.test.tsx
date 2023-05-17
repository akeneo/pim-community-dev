import {renderHook} from '@testing-library/react-hooks';
import {useGetIdentifierGenerators} from '../useGetIdentifierGenerators';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {ServerError} from '../../errors';
import {rest} from 'msw';
import {server} from '../../mocks/server';
import mockedIdentifierGenerators from '../../tests/fixtures/identifierGenerators';

describe('useGetIdentifierGenerators', () => {
  test('it retrieves generators list', async () => {
    const {result, waitFor} = renderHook(() => useGetIdentifierGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toBeDefined();
    expect(result.current.data).toEqual(mockedIdentifierGenerators);
  });

  test('it fails and retrieves no data', async () => {
    server.use(
      rest.get('/akeneo_identifier_generator_rest_list', (req, res, ctx) => {
        return res(ctx.status(500), ctx.json({}));
      })
    );
    const {result, waitFor} = renderHook(() => useGetIdentifierGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.error);

    expect(result.current.error).toBeDefined();
    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});
