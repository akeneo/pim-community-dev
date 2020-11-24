'use strict';

const BaseFetcher = require('pim/base-fetcher');
const requireContext = require('require-context');

type FetcherList = {
  [fetcherName: string]: {
    loadedModule: any;
    module: string;
    options: unknown;
  };
};

class FetcherRegistry {
  private fetchers: FetcherList = {};
  private initializePromise: null | Promise<void> = null;

  /**
   * @return Promise
   */
  initialize() {
    if (!this.initializePromise) {
      this.initializePromise = new Promise<void>(resolve => {
        const fetcherList = __moduleConfig.fetchers as {[fetcherName: string]: any};
        const defaultFetcher = 'pim/base-fetcher';
        const fetchers: FetcherList = {};

        Object.keys(fetcherList).forEach(name => {
          const config = typeof fetcherList[name] === 'string' ? {module: fetcherList[name]} : fetcherList[name];
          config.options = config.options || {};
          fetchers[name] = config;
        });

        for (const fetcher in fetcherList) {
          const moduleName = fetcherList[fetcher].module || defaultFetcher;
          const ResolvedModule = requireContext(moduleName);
          fetchers[fetcher].loadedModule = new ResolvedModule(fetchers[fetcher].options);
          fetchers[fetcher].options = fetcherList[fetcher].options;
        }

        this.fetchers = fetchers;
        resolve();
      });
    }

    return this.initializePromise;
  }

  /**
   * Get the related fetcher for the given collection name
   *
   * @return Fetcher
   */
  getFetcher(fetcherName: string) {
    if (null === this.initializePromise) {
      throw new Error('Cannot call getFetcher before fetcherRegistry initialization');
    }
    var fetcher = this.fetchers[fetcherName] || this.fetchers.default;

    return fetcher.loadedModule;
  }

  /**
   * Clear the fetcher cache for the given collection name
   */
  clear(entityType: string, entity: string | number): void {
    this.getFetcher(entityType).clear(entity);
  }

  /**
   * Clear all fetchers cache
   */
  clearAll(): void {
    Object.values(this.fetchers).forEach(fetcher => {
      fetcher.loadedModule.clear();
    });
  }
}

module.exports = new FetcherRegistry();
