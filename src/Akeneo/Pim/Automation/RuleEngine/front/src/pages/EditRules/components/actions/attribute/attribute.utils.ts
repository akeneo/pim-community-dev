import { useEffect } from 'react';
import { getAttributeByIdentifier } from '../../../../../repositories/AttributeRepository';
import { Translate, Router } from '../../../../../dependenciesTools';
import { AttributeCode, Attribute } from '../../../../../models';

const validateAttribute = (translate: Translate, router: Router) => async (
  value: any
) => {
  if (!value) {
    return translate('pimee_catalog_rule.exceptions.required_attribute');
  }
  const attribute = await getAttributeByIdentifier(value, router);
  if (null === attribute) {
    return `${translate(
      'pimee_catalog_rule.exceptions.unknown_attribute'
    )} ${translate(
      'pimee_catalog_rule.exceptions.select_another_attribute_or_remove_action'
    )}`;
  }
  return true;
};

const fetchAttribute = async (router: Router, attributeCode: AttributeCode) => {
  return await getAttributeByIdentifier(attributeCode, router);
};

const useGetAttributeAtMount = (
  attributeCode: AttributeCode | null,
  router: Router,
  attribute?: Attribute | null,
  setAttribute?: (val: Attribute | null | undefined) => void
) => {
  useEffect(() => {
    const getAttribute = async (
      router: Router,
      attributeCode: AttributeCode
    ) => {
      const attribute = await fetchAttribute(router, attributeCode);
      if (setAttribute && attribute) {
        setAttribute(attribute);
      } else if (setAttribute) {
        setAttribute(null);
      }
    };
    if (attributeCode && !attribute) {
      getAttribute(router, attributeCode);
    }
  }, []);
};

const createAttributeLink = (
  router: Router,
  attributeCode?: AttributeCode | null
) =>
  `#${router.generate(`pim_enrich_attribute_index`, {
    open_create_attribute_modal: '1',
    code: attributeCode ?? '',
  })}`;

export {
  validateAttribute,
  useGetAttributeAtMount,
  fetchAttribute,
  createAttributeLink,
};
