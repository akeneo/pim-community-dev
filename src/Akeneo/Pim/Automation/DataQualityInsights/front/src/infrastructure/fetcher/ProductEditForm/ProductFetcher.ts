import {Product} from '../../../domain';

export default interface ProductFetcher {
  (productId: string): Promise<Product>;
}
