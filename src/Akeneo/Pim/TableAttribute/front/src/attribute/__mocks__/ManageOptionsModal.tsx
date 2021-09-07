import React from 'react';
import {SelectOption} from '../../models';

type ManageOptionsModalProps = {
  onChange: (options: SelectOption[]) => void;
};

const ManageOptionsModal: React.FC<ManageOptionsModalProps> = ({onChange}) => {
  const fakeSave = () => {
    onChange([{code: 'fake_code', labels: {en_US: 'fake label '}}]);
  };

  return <button onClick={fakeSave}>Fake confirm</button>;
};

export {ManageOptionsModal};
