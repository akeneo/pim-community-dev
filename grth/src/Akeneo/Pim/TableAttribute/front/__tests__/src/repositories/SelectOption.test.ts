import {SelectOptionRepository} from '../../../src';
import 'jest-fetch-mock';
import {getComplexTableAttribute} from '../../factories';

describe('SelectOption', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    SelectOptionRepository.clearCache();
  });

  it('should catch errors from backend on save', async () => {
    global.console = {...global.console, error: jest.fn()};

    const router = {
      generate: jest.fn(),
      redirect: jest.fn(),
      redirectToRoute: jest.fn(),
    };
    fetchMock.mockAbortOnce();

    expect(await SelectOptionRepository.save(router, getComplexTableAttribute(), 'ingredient', [])).toBeFalsy();
    expect(console.error).toBeCalled();
  });
});
