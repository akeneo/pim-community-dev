/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributesMapping} from './model/attributes-mapping';

export const isAlreadyMapped = (
  mapping: AttributesMapping,
  franklinAttributeCode: string,
  attributeCode: string | null
): boolean => {
  if (null === attributeCode) {
    return false;
  }
  return (
    undefined !==
    Object.values(mapping).find(
      mapping => mapping.franklinAttribute.code !== franklinAttributeCode && mapping.attribute === attributeCode
    )
  );
};
