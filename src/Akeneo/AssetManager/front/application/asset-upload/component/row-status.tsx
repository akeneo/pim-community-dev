import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

const StatusLabel = styled.span`
  color: 1px solid ${(props: ThemedProps<{color: string}>) => props.color};
  border: 1px solid ${(props: ThemedProps<{color: string}>) => props.color};
  text-transform: uppercase;
  border-radius: 2px;
  padding: 0 4px;
  font-size: 11px;
`;
const ProgressBar = styled.div`
  background: ${(props: ThemedProps<void>) => props.theme.color.grey80};
  height: 18px;
  overflow: hidden;
  position: relative;
  width: 120px;
`;
const ProgressBarFill = styled.div<{width: number}>`
  background: ${(props: ThemedProps<{width: number}>) => props.theme.color.blue100};
  height: 18px;
  left: 0;
  position: absolute;
  top: 0;
  width: ${(props: ThemedProps<{width: number}>) => props.width}%;
`;

type LineStatusProps = {
  status: LineStatus;
  progress: number | null;
};

const RowStatus = ({status, progress}: LineStatusProps) => {
  switch (status) {
    case LineStatus.WaitingForUpload:
    case LineStatus.Incomplete:
      return (
        <StatusLabel color={akeneoTheme.color.grey100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Ready:
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
          <ProgressBarFill width={Math.round((progress || 0) * 100)} />
        </ProgressBar>
      );
    default:
      throw Error('unsupported line status');
  }
};

export default RowStatus;
