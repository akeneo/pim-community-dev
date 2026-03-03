import {useState} from 'react';

const useSelection = () => {
  const [checked, setChecked] = useState<boolean>(false);

  return {checked, onChange: () => setChecked(!checked)};
};

export {useSelection};
