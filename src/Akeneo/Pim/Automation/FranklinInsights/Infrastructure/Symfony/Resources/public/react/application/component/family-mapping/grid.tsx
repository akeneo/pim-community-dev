/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';

import {Translate} from '../shared/translate';
import {Row} from './row';
import {AttributeMapping} from '../../../domain/model/attribute-mapping';
import {Toolbar} from './toolbar';

interface Props {
  mapping: AttributeMapping[];
  selectedFranklinAttributeCodes: string[];
}

export const Grid = ({mapping, selectedFranklinAttributeCodes}: Props) => {
  return (
    <div className='AknGridContainer AknGridContainer--withCheckbox'>
      <table className='AknGrid AknGrid--unclickable AknGrid--withCheckbox attribute-mapping'>
        <thead className='AknGrid-header'>
          <tr className='AknGrid-bodyRow'>
            <th className='AknGrid-headerCell AknGrid-headerCell--checkbox'></th>
            <th className='AknGrid-headerCell'>
              <Translate id='akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_attribute' />
            </th>
            <th className='AknGrid-headerCell catalog-attribute'>
              <Translate id='akeneo_franklin_insights.entity.identifier_mapping.fields.catalog_attribute' />
            </th>
            <th className='AknGrid-headerCell'></th>
            <th className='AknGrid-headerCell'></th>
          </tr>
        </thead>

        <tbody className='AknGrid-body'>
          {mapping.map(attributeMapping => (
            <Row
              key={attributeMapping.franklinAttribute.code}
              franklinAttributeCode={attributeMapping.franklinAttribute.code}
              mapping={attributeMapping}
            />
          ))}
        </tbody>
      </table>

      {0 < selectedFranklinAttributeCodes.length && (
        <Toolbar selectedFranklinAttributeCodes={selectedFranklinAttributeCodes} />
      )}
    </div>
  );
};
