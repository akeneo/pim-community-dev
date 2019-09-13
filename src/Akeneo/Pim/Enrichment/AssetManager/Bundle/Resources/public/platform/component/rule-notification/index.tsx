import * as React from 'react';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {RuleRelation} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';
import {getRulesForAttribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';
import IconInfoIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/icon/info';
import {NotificationSection, NotificationText} from 'akeneopimenrichmentassetmanager/platform/component/common/notification';
import {Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';

const Rule = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.blue80};
  margin-left: 5px;
`;

type RuleNotificationProps = {
  attributeCode: AttributeCode,
  ruleRelations: RuleRelation[]
};

export const RuleNotification = ({attributeCode, ruleRelations}: RuleNotificationProps) => {
  const ruleCodes = getRulesForAttribute(attributeCode, ruleRelations);

  if (ruleCodes.length === 0) {
    return null;
  }

  return (
    <NotificationSection>
      <IconInfoIllustration />
      <Separator />
      <NotificationText>{__('pim_asset_manager.asset_collection.notification.product_rule')} <Rule>{ruleCodes.join(', ')}</Rule></NotificationText>
    </NotificationSection>
  );
}
