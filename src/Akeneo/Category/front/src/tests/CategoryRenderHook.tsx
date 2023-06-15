import {renderHook} from '@testing-library/react-hooks';
import {CategoryProviders} from './CategoryProviders';

export const renderHookWithCategoryProviders = (hook: () => any) => renderHook(hook, {wrapper: CategoryProviders});
