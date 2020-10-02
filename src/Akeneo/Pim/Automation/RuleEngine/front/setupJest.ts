import 'mutationobserver-shim';
import 'jest-fetch-mock';
import { GlobalWithFetchMock } from 'jest-fetch-mock';
import 'core-js';

const customGlobal: GlobalWithFetchMock = (global as unknown) as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

jest.mock('./src/components/Select2Wrapper/Select2Wrapper');
jest.mock('./src/dependenciesTools/provider/dependencies.ts');
jest.mock('./src/fetch/categoryTree.fetcher.ts');
jest.mock(
  './src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);
jest.mock('./src/dependenciesTools/components/AssetManager/AssetSelector');
