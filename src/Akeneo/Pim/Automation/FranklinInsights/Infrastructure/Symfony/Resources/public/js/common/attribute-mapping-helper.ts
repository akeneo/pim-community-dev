/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import AttributeMappingStatus from '../model/attribute-mapping-status';

const SecurityContext = require('pim/security-context');

const hasPermissionToCreateAttribute = (): boolean =>
  SecurityContext.isGranted('pim_enrich_attribute_create') &&
  SecurityContext.isGranted('pim_enrich_family_edit_attributes');

/**
 * Check if the user is able to create an attribute.
 */
export const isAbleToCreateAttribute = (
  code: string | null,
  status: AttributeMappingStatus,
  canCreateAttribute: boolean
): boolean => {
  if (false === hasPermissionToCreateAttribute()) {
    return false;
  }
  if (AttributeMappingStatus.ATTRIBUTE_PENDING !== status) {
    return false;
  }
  if (null !== code && '' !== code) {
    return false;
  }
  if (false === canCreateAttribute) {
    return false;
  }

  return true;
};

const hasPermissionToAddAttributeToFamily = (): boolean =>
  SecurityContext.isGranted('pim_enrich_family_edit_attributes');

/**
 * Check if the user is able to add an attribute to the family.
 *
 * @param exactMatchAttributeFromOtherFamily Attribute code of an existing attribute in another family that is an exact
 *                                           match for this franklin attribute.
 */
export const isAbleToAddAttributeToFamily = (
  attributeCode: string | null,
  status: AttributeMappingStatus,
  exactMatchAttributeFromOtherFamily: string | null
): boolean => {
  if (false === hasPermissionToAddAttributeToFamily()) {
    return false;
  }
  if (AttributeMappingStatus.ATTRIBUTE_PENDING !== status) {
    return false;
  }
  if (null !== attributeCode && '' !== attributeCode) {
    return false;
  }
  if (null === exactMatchAttributeFromOtherFamily) {
    return false;
  }

  return true;
};
