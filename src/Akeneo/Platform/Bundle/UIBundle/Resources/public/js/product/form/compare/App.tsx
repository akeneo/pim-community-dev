import React, {FC} from 'react';
import {Button, Modal, useBooleanState, SectionTitle} from 'akeneo-design-system';
import {Product} from '@akeneo-pim-community/enrichment';

type Props = {
  product: Product; // @todo define a proper type?
  attributeGroups: any
};
const CompareApp: FC<Props> = ({product, attributeGroups}) => {

  console.log('product data', product);
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <>
      <Button onClick={() => (isOpen ? close() : open())}>Compare</Button>

      {isOpen && (
        <Modal closeTitle="Close Compare" onClose={close}>
          <div>
            {Object.entries(attributeGroups).map(([key, attributeGroup]) => (
              <div key={key}>
                <SectionTitle>
                  <SectionTitle.Title>{attributeGroup.labels['en_US']}</SectionTitle.Title>
                </SectionTitle>
              </div>
            ))}
          </div>
        </Modal>
      )}
    </>
  );
};
export {CompareApp};
