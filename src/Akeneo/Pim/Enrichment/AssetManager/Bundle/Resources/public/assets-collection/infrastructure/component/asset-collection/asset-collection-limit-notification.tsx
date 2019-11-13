import * as React from 'react';
import {
  NotificationSection,
  NotificationText,
} from 'akeneopimenrichmentassetmanager/platform/component/common/notification';
import {Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';
import IconInfoIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/icon/info';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ASSET_COLLECTION_LIMIT} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';

const NotShrinkableIcon = styled.div`
  flex-shrink: 0;
  height: 24px;
`;

export const AssetCollectionLimitNotification = ({limit = ASSET_COLLECTION_LIMIT}: {limit?: number}) => (
  <NotificationSection>
    <NotShrinkableIcon>
      <IconInfoIllustration />
    </NotShrinkableIcon>
    <Separator />
    <NotificationText>{__('pim_asset_manager.asset_collection.notification.limit', {limit: limit})}</NotificationText>
  </NotificationSection>
);
