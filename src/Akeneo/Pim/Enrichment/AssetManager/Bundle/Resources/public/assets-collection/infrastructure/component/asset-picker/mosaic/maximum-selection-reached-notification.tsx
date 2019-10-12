import * as React from 'react';
import {
  NotificationSection,
  NotificationText,
} from 'akeneopimenrichmentassetmanager/platform/component/common/notification';
import {Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';
import IconInfoIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/icon/info';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';

const NotShrinkableIcon = styled.div`
  flex-shrink: 0;
  height: 24px;
`;

const MaximumSelectionReachedNotification = ({maxSelectionCount}: {maxSelectionCount: number}) => {
  return (
    <NotificationSection>
      <NotShrinkableIcon>
        <IconInfoIllustration />
      </NotShrinkableIcon>
      <Separator />
      <NotificationText>
        {__('pim_asset_manager.asset_picker.notification.maximum_selection_reached', {
          maxSelectionCount: maxSelectionCount,
        })}
      </NotificationText>
    </NotificationSection>
  );
};

export default MaximumSelectionReachedNotification;
