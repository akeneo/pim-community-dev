import * as React from 'react';
import {NotificationSection, NotificationText} from 'akeneoassetmanager/platform/component/common/notification';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import IconInfoIllustration from 'akeneoassetmanager/platform/component/visual/icon/info';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ASSET_COLLECTION_LIMIT} from 'akeneoassetmanager/domain/model/asset/list-asset';

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
