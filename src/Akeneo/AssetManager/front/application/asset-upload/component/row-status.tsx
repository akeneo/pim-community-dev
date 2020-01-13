import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

const StatusLabel = styled.span`
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<{color: string}>) => props.color};
  color: ${(props: ThemedProps<{color: string}>) => props.color};
  display: inline-block;
  font-size: 11px;
  height: 18px;
  line-height: 16px;
  max-width: 100%;
  overflow: hidden;
  padding: 0 4px;
  text-align: center;
  text-overflow: ellipsis;
  text-transform: uppercase;
  white-space: nowrap;
`;
const ProgressBar = styled.div`
  background: ${(props: ThemedProps<void>) => props.theme.color.grey80};
  border-radius: 2px;
  height: 18px;
  overflow: hidden;
  position: relative;
  width: 120px;
`;
const ProgressBarFill = styled.div<{width: number}>`
  background: ${(props: ThemedProps<{width: number}>) => props.theme.color.blue100};
  border-radius: 2px;
  height: 18px;
  left: 0;
  position: absolute;
  top: 0;
  transition: width 0.3s;
  width: ${(props: ThemedProps<{width: number}>) => props.width}%;
`;

const progressToWidth = (progress: number | null): number => {
  if (null === progress || Number.isNaN(progress) || progress < 0) {
    return 0;
  }
  if (progress > 1) {
    return 100;
  }
  return Math.round(progress * 100);
};

type RowStatusProps = {
  status: LineStatus;
  progress: number | null;
};

const RowStatus = React.memo(({status, progress}: RowStatusProps) => {
  switch (status) {
    case LineStatus.WaitingForUpload:
      return (
        <StatusLabel color={akeneoTheme.color.grey100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Valid:
    case LineStatus.Created:
      return (
        <StatusLabel color={akeneoTheme.color.green100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Invalid:
      return (
        <StatusLabel color={akeneoTheme.color.red100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Uploaded:
      return (
        <StatusLabel color={akeneoTheme.color.blue100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.UploadInProgress:
      return (
        <ProgressBar>
          <ProgressBarFill
            title={__('pim_asset_manager.asset.upload.status.' + status)}
            width={progressToWidth(progress)}
          />
        </ProgressBar>
      );
    default:
      throw Error('unsupported line status');
  }
});

export default RowStatus;
