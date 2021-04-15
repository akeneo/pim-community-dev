import React from 'react';
import styled from 'styled-components';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {getRulesForAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {getColor, Helper} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';

const securityContext = require('pim/security-context');

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
  const rulesForAttribute: number = getRulesForAttribute(attributeCode, rulesNumberByAttribute);
  const router = useRouter();

  if (rulesForAttribute === 0) {
    return null;
  }

  const translation = securityContext.isGranted('pimee_catalog_rule_rule_view_permissions')
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
        dangerouslySetInnerHTML={{
          __html: translate(translation, {count: rulesForAttribute.toString()}, rulesForAttribute),
        }}
      />
    </Helper>
  );
};

export {RuleNotification};
