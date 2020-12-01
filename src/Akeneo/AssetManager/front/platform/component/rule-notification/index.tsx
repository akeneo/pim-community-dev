import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {RuleRelation} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {getRulesForAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {NotificationSection, NotificationText} from 'akeneoassetmanager/platform/component/common/notification';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {getColor, InfoRoundIcon} from 'akeneo-design-system';

const Rule = styled.div`
  color: ${getColor('blue', 80)};
  margin-left: 5px;
`;

type RuleNotificationProps = {
  attributeCode: AttributeCode;
  ruleRelations: RuleRelation[];
};

//TODO RAC-413 replace this with a Helper
export const RuleNotification = ({attributeCode, ruleRelations}: RuleNotificationProps) => {
  const ruleCodes = getRulesForAttribute(attributeCode, ruleRelations);

  if (ruleCodes.length === 0) {
    return null;
  }

  return (
    <NotificationSection>
      <InfoRoundIcon />
      <Separator />
      <NotificationText>
        {__('pim_asset_manager.asset_collection.notification.product_rule')} <Rule>{ruleCodes.join(', ')}</Rule>
      </NotificationText>
    </NotificationSection>
  );
};
