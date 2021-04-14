import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';

const Index = ({jobCode}: {jobCode: string}) => {
  const [test, open, close] = useBooleanState();

  return (
    <Button level="secondary" onClick={test ? close : open}>
      Hello Pierre: {jobCode}! {test ? 'nice' : 'cool'}
    </Button>
  );
};

export default Index;
