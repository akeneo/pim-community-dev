/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for ConnectionStatus
 * Akeneo/Pim/Automation/SuggestData/Infrastructure/Controller/Normalizer/InternalApi/ConnectionStatusNormalizer.php
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
export default interface ConnectionStatus {
  isActive: boolean;
  isIdentifiersMappingValid: boolean;
  productSubscriptionCount: number;
}
