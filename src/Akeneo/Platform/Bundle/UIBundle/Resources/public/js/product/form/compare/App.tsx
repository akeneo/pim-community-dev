import React, {FC} from 'react';
import {Button, Modal, useBooleanState} from 'akeneo-design-system';
import {Product} from '@akeneo-pim-community/enrichment';

type Props = {
  product: Product; // @todo define a proper type?
};
const CompareApp: FC<Props> = ({product}) => {
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <>
      <Button onClick={() => (isOpen ? close() : open())}>Compare</Button>

      {isOpen && (
        <Modal closeTitle="Close Compare" onClose={close}>
          <div>
            Product:
            <pre>{JSON.stringify(product)}</pre>
          </div>
        </Modal>
      )}
    </>
  );
};
export {CompareApp};
