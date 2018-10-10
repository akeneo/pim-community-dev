/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
