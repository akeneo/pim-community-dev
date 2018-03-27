import Fetcher from 'pimfront/app/infrastructure/fetcher/fetcher';
import {AttributeInterface} from 'pimfront/product-grid/domain/model/field';
import hidrator from 'pimfront/app/application/hidrator/attribute';
const fetcherRegistry = require('pimenrich/js/fetcher/fetcher-registry');

export interface AttributeFetcher extends Fetcher<AttributeInterface> {}

export class BaseAttributeFetcher implements AttributeFetcher {
  constructor(private fetcherRegistry: any, private hidrator: (backendAttribute: any) => AttributeInterface) {}

  async fetch(identifier: string): Promise<AttributeInterface> {
    const backendAttribute = await this.fetcherRegistry.getFetcher('attribute').fetch(identifier);

    return this.hidrator(backendAttribute);
  }
}

export default new BaseAttributeFetcher(fetcherRegistry, hidrator);
