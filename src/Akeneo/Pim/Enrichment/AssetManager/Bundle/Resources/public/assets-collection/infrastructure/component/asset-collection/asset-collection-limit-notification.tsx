import React from 'react';
import {NotificationSection, NotificationText} from 'akeneoassetmanager/platform/component/common/notification';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ASSET_COLLECTION_LIMIT} from 'akeneoassetmanager/domain/model/asset/list-asset';
import {InfoRoundIcon} from 'akeneo-design-system';

const NotShrinkableIcon = styled.div`
  flex-shrink: 0;
  height: 24px;
`;

//TODO RAC-413 replace this with a Helper
export const AssetCollectionLimitNotification = ({limit = ASSET_COLLECTION_LIMIT}: {limit?: number}) => (
  <NotificationSection>
    <NotShrinkableIcon>
      <InfoRoundIcon />
    </NotShrinkableIcon>
    <Separator />
    <NotificationText>{__('pim_asset_manager.asset_collection.notification.limit', {limit: limit})}</NotificationText>
  </NotificationSection>
);
