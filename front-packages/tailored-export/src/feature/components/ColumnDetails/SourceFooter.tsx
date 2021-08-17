import React from 'react';
import styled from 'styled-components';
import {Button, getColor, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models';

const Container = styled.div`
  position: sticky;
  bottom: 0;
  background: ${getColor('white')};
  padding-top: 10px;
  display: flex;
  justify-content: flex-end;
`;

type SourceFooterProps = {
  source: Source;
  onSourceRemove: (source: Source) => void;
};

const SourceFooter = ({source, onSourceRemove}: SourceFooterProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  const handleConfirm = () => {
    close();
    onSourceRemove(source);
  };

  return (
    <Container>
      <Button level="danger" ghost onClick={open}>
        {translate('akeneo.tailored_export.column_details.sources.remove.button')}
      </Button>
      {isOpen && (
        <DeleteModal
          title={translate('akeneo.tailored_export.column_details.sources.remove.title')}
          confirmButtonLabel={translate('pim_common.confirm')}
          onConfirm={handleConfirm}
          onCancel={close}
        >
          {translate('akeneo.tailored_export.column_details.sources.remove.text')}
        </DeleteModal>
      )}
    </Container>
  );
};

export {SourceFooter};
