import React from 'react';
import { Label } from '../Labels';

type Props = React.InputHTMLAttributes<HTMLTextAreaElement> & {
  label: string;
};

const InputTextArea: React.FC<Props> = ({ label, ...remainingProps }) => {
  return (
    <>
      <Label label={label} />
      <textarea className='AknTextareaField' {...remainingProps} />
    </>
  );
};

InputTextArea.displayName = 'InputTextArea';

export { InputTextArea };
