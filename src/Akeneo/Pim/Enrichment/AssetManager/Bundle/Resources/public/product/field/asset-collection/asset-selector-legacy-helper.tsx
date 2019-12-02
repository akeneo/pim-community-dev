import * as React from 'react';
import styled from 'styled-components';
import IconInfoIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/icon/info';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {
  NotificationSection,
  NotificationText,
} from 'akeneopimenrichmentassetmanager/platform/component/common/notification';
import __ from 'akeneoassetmanager/tools/translator';

const Container = styled.div`
  width: 460px;
`;

const IconContainer = styled.div`
  align-self: center;
  flex-shrink: 0;
  height: 24px;
`;

type AssetSelectorLegacyHelperProps = {
  label: string;
  isMissingRequired: boolean;
};

const AssetSelectorLegacyHelper = ({label, isMissingRequired}: AssetSelectorLegacyHelperProps) => {
  return (
    <Container>
      <NotificationSection>
        <IconContainer>
          <IconInfoIllustration />
        </IconContainer>
        <Separator />
        <NotificationText>
          {isMissingRequired ? (
            <div>
              {__(`pim_asset_manager.asset_collection.legacy_helper.is_missing_required`, {
                label: label,
              })}
            </div>
          ) : (
            <div>
              {__(`pim_asset_manager.asset_collection.legacy_helper.moved_to_assets_tab`, {
                label: label,
              })}
            </div>
          )}
        </NotificationText>
      </NotificationSection>
    </Container>
  );
};

export default AssetSelectorLegacyHelper;
