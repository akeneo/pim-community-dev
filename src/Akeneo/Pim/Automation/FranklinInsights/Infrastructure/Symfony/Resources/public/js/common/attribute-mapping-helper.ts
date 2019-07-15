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

const hasPermissionToCreateAttribute = () =>
  SecurityContext.isGranted('pim_enrich_attribute_create') &&
  SecurityContext.isGranted('pim_enrich_family_edit_attributes');

export const isAbleToCreateAttribute = (
  code: string | null,
  status: AttributeMappingStatus,
  canCreateAttribute: boolean
): boolean => {
  if (false === hasPermissionToCreateAttribute()) {
    return false;
  }
  if (null !== code && '' !== code) {
    return false;
  }
  if (AttributeMappingStatus.ATTRIBUTE_PENDING !== status) {
    return false;
  }
  if (false === canCreateAttribute) {
    return false;
  }

  return true;
};
