import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, Badge, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';

const ProgressBar = styled.div`
  background: ${getColor('grey', 80)};
  border-radius: 2px;
  height: 18px;
  overflow: hidden;
  position: relative;
  width: 120px;
`;
const ProgressBarFill = styled.div<{width: number} & AkeneoThemedProps>`
  background: ${getColor('blue', 100)};
  border-radius: 2px;
  height: 18px;
  left: 0;
  position: absolute;
  top: 0;
  transition: width 0.3s;
  width: ${({width}) => width}%;
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
  const translate = useTranslate();
  const statusLabel = translate(`pim_asset_manager.asset.upload.status.${status}`);

  switch (status) {
    case LineStatus.WaitingForUpload:
      return <Badge level="tertiary">{statusLabel}</Badge>;
    case LineStatus.Valid:
    case LineStatus.Created:
      return <Badge level="primary">{statusLabel}</Badge>;
    case LineStatus.Invalid:
      return <Badge level="danger">{statusLabel}</Badge>;
    case LineStatus.Uploaded:
      return <Badge level="secondary">{statusLabel}</Badge>;
    case LineStatus.UploadInProgress:
      return (
        <ProgressBar>
          <ProgressBarFill title={statusLabel} width={progressToWidth(progress)} />
        </ProgressBar>
      );
    default:
      throw Error('unsupported line status');
  }
});

export default RowStatus;
