import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useActivatedLocales} from '../../../../../src/hooks/locales';
import {aListOfLocales} from '../../../utils/provideLocaleHelper';
import {fetchActivatedLocales} from '../../../../../src/infrastructure/fetchers';
import {act} from 'react-test-renderer';

jest.mock('@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/localesFetcher');

describe('useInitialLocalesDataGridState', () => {
  const renderUseInitialLocalesIndexState = () => {
    return renderHookWithProviders(useActivatedLocales);
  };
  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.resetAllMocks();
  });

  test('it initializes the state for Locales datagrid', () => {
    const {result} = renderUseInitialLocalesIndexState();

    expect(result.current.locales).toEqual([]);
    expect(result.current.load).toBeDefined();
    expect(result.current.isPending).toBeTruthy();
  });

  test('it loads the activated locales', async () => {
    const activatedLocales = aListOfLocales(['en_US', 'fr_FR', 'en_US']);

    // @ts-ignore
    fetchActivatedLocales.mockResolvedValue(activatedLocales);

    const {result} = renderUseInitialLocalesIndexState();

    expect(result.current.isPending).toBeTruthy();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.isPending).toBeFalsy();

    expect(result.current.locales).toBe(activatedLocales);
  });
});
