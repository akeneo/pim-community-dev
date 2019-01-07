/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface and constants for model of normalized Attribute Options Mapping.
 * This file defines the contract of the format received and send to the back-end.
 * @see Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\AttributeOptionsMappingController
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
export enum AttributeOptionStatus {
  Pending = 0,
  Mapped = 1,
  Unmapped = 2,
}

export interface NormalizedAttributeOptionsMapping {
  family: string;
  franklinAttributeCode: string;
  catalogAttributeCode: string;
  mapping: {
    [franklinAttributeOptionCode: string]: {
      franklinAttributeOptionCode: {
        label: string;
      },
      catalogAttributeOptionCode: string;
      status: number;
    },
  };
}
