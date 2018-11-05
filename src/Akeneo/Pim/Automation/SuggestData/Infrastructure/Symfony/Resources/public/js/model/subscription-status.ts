/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
export default interface SubscriptionStatus {
  isConnectionActive: boolean;
  isSubscribed: boolean;
  isIdentifiersMappingValid: boolean;
  hasFamily: boolean;
  isMappingFilled: boolean;
  isProductVariant: boolean;
}
