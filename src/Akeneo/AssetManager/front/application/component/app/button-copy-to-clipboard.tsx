import * as React from 'react';
import {useRef} from 'react';

const ButtonCopyToClipboard = ({
  value,
  ...props
}: {
  value: string;
} & any) => {
  const inputRef = useRef<HTMLInputElement>(null);

  const copyToClipboard = () => {
    if (null === inputRef.current) return;

    inputRef.current.select();
    document.execCommand('copy');
  };

  return (
    <React.Fragment>
      <input type="text" value={value} ref={inputRef} style={{position: 'absolute', left: -9999}} />
      <button {...props} onClick={copyToClipboard} />
    </React.Fragment>
  );
};

export default ButtonCopyToClipboard;
