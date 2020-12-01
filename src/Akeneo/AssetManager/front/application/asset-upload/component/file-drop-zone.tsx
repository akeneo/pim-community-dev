import React from 'react';
import styled from 'styled-components';
import {ImportIllustration, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  margin: 10px 0 24px;
  position: relative;

  &:hover {
    svg {
      .arrow {
        transform: rotate(180deg);
      }

      .stars {
        transform: scale(1.2);
      }
    }
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
  padding: 30px 0 0;

  svg {
    overflow: visible;
    height: 60px;
    width: 80px;

    .arrow {
      transform-origin: 51.2% 26%;
      transition: transform 0.3s ease-in-out;
    }

    .stars {
      transform-origin: 50% 50%;
      transition: transform 0.2s linear;
    }
  }
`;

const UploaderHelper = styled.div`
  margin: 17px 0 0;
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
        <UploaderHelper>{translate(`pim_asset_manager.asset.upload.drop_or_click_here`)}</UploaderHelper>
      </Uploader>
    </Container>
  );
});

export default FileDropZone;
