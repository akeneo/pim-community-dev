import React from 'react';
import styled, {FlattenSimpleInterpolation} from 'styled-components';
import {ImportIllustration, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  margin: 10px 0 24px;
  position: relative;

  :hover {
    ${ImportIllustration.animatedMixin as FlattenSimpleInterpolation}
  }
`;

const FileInput = styled.input`
  cursor: pointer;
  height: 100%;
  opacity: 0;
  position: absolute;
  width: 100%;
  z-index: 1;
`;

const Uploader = styled.div`
  align-items: center;
  border: 1px solid ${getColor('grey', 80)};
  display: flex;
  flex-direction: column;
  height: 140px;
  justify-content: start;
  width: 100%;
  padding: 20px 0;
  color: ${getColor('grey', 140)};
`;

type FileDropZoneProps = {
  onDrop: (event: React.ChangeEvent<HTMLInputElement>) => void;
};

const FileDropZone = React.memo(({onDrop}: FileDropZoneProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <FileInput
        type="file"
        multiple
        onChange={onDrop}
        aria-label={translate('pim_asset_manager.asset.upload.drop_or_click_here')}
      />
      <Uploader>
        <ImportIllustration />
        {translate(`pim_asset_manager.asset.upload.drop_or_click_here`)}
      </Uploader>
    </Container>
  );
});

export default FileDropZone;
