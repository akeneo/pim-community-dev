import React from 'react';
import styled from 'styled-components';
import {getColor, Helper} from 'akeneo-design-system';
import {useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {getRulesForAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';

const HelperContent = styled.div`
  span {
    cursor: pointer;
    color: ${getColor('blue', 80)};
    text-decoration: underline;
  }
`;

type RuleNotificationProps = {
  attributeCode: AttributeCode;
  rulesNumberByAttribute: RulesNumberByAttribute;
};

const RuleNotification = ({attributeCode, rulesNumberByAttribute}: RuleNotificationProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const rulesForAttribute: number = getRulesForAttribute(attributeCode, rulesNumberByAttribute);
  const router = useRouter();

  if (rulesForAttribute === 0) {
    return null;
  }

  const translation = isGranted('pimee_catalog_rule_rule_view_permissions')
    ? 'pimee_enrich.entity.product.module.attribute.can_be_updated_by_rules'
    : 'pimee_enrich.entity.product.module.attribute.can_be_updated_by_rules_readonly';

  const redirectToAttributeRules = (event: any) => {
    if (event.target.tagName === 'SPAN') {
      sessionStorage.setItem('current_form_tab', 'pim-attribute-edit-form-rules-tab');
      router.redirect(router.generate('pim_enrich_attribute_edit', {code: attributeCode}));
    }
  };

  return (
    <Helper>
      <HelperContent
        onClick={redirectToAttributeRules}
        dangerouslySetInnerHTML={{__html: translate(translation, {count: rulesForAttribute}, rulesForAttribute)}}
      />
    </Helper>
  );
};

export {RuleNotification};
