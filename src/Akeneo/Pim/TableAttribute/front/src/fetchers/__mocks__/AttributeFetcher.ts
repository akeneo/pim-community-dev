import {Router} from '@akeneo-pim-community/shared';
import {getTableAttribute} from '../../../__tests__/src/factories/Attributes';
import {Attribute} from '../../models/Attribute';
import {getComplexTableConfiguration} from '../../../__tests__/src/factories/TableConfiguration';

const fetchAttribute = async (_router: Router, attributeCode: string): Promise<Attribute> => {
  if (attributeCode === 'nutrition') {
    return new Promise(resolve =>
      resolve({...getTableAttribute(), table_configuration: getComplexTableConfiguration()})
    );
  }

  throw new Error(`Non mocked attribute ${attributeCode}`);
};

export {fetchAttribute};
