import React from 'react';
import styled from 'styled-components';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {NotificationSection, NotificationText} from 'akeneoassetmanager/platform/component/common/notification';
import __ from 'akeneoassetmanager/tools/translator';
import {InfoRoundIcon} from 'akeneo-design-system';

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

//TODO RAC-413 replace this with a Helper
const AssetSelectorLegacyHelper = ({label, isMissingRequired}: AssetSelectorLegacyHelperProps) => {
  return (
    <Container>
      <NotificationSection>
        <IconContainer>
          <InfoRoundIcon />
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
