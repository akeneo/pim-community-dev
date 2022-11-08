import React from 'react';
import {Delimiter} from '../../models';
import {Preview as PreviewComponent} from 'akeneo-design-system';
import {PropertyPreview} from './preview/PropertyPreview';
import {DelimiterPreview} from './preview/DelimiterPreview';
import {StructureWithIdentifiers} from '../Structure';

type PreviewProps = {
  structure: StructureWithIdentifiers;
  delimiter: Delimiter | null;
};

const Preview: React.FC<PreviewProps> = ({structure, delimiter}) => {
  return (
    <PreviewComponent title={'Preview'}>
      {structure.map((item, i) => (
        <React.Fragment key={item.id}>
          {i > 0 && delimiter && <DelimiterPreview delimiter={delimiter} />}
          <PropertyPreview property={item} />
        </React.Fragment>
      ))}
    </PreviewComponent>
  );
};

export {Preview};
