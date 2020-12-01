import React from 'react';
import styled from 'styled-components';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {RuleRelation} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {getRulesForAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {getColor, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const RuleCodes = styled.span`
  color: ${getColor('blue', 80)};
  margin-left: 5px;
`;

type RuleNotificationProps = {
  attributeCode: AttributeCode;
  ruleRelations: RuleRelation[];
};

const RuleNotification = ({attributeCode, ruleRelations}: RuleNotificationProps) => {
  const translate = useTranslate();
  const ruleCodes = getRulesForAttribute(attributeCode, ruleRelations);

  if (ruleCodes.length === 0) {
    return null;
  }

  return (
    <Helper>
      {translate('pim_asset_manager.asset_collection.notification.product_rule')}
      <RuleCodes>{ruleCodes.join(', ')}</RuleCodes>
    </Helper>
  );
};

export {RuleNotification};
