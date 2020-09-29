import {Product} from '../../../domain';

export default interface ProductFetcher {
  (productId: number): Promise<Product>;
}
