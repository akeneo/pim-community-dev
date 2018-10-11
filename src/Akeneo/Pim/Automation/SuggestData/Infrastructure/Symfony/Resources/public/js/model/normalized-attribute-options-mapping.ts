/*
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
 * @see Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\AttributeOptionsMappingController
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
export enum ATTRIBUTE_OPTION_STATUS {
  PENDING = 0,
  MAPPED = 1,
  UNMAPPED = 2,
}

export interface NormalizedAttributeOptionsMapping {
  family: string;
  franklin_attribute_code: string;
  mapping: {
    [pimAiAttributeOptionCode: string]: {
      franklin_attribute_option_code: {
        label: string;
      },
      catalog_attribute_option_code: string;
      status: number;
    },
  };
}
