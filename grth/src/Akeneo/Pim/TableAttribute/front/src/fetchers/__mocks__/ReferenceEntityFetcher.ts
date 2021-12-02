import {Router} from '@akeneo-pim-community/shared';
import {ReferenceEntity} from '../../models';

const image = {
  filePath: '/a/b/c/img.png',
  originalFilename: 'img.png',
};

const fetchAll = async (_router: Router): Promise<ReferenceEntity[]> => {
  return Promise.resolve([
    {identifier: 'brand', labels: {en_US: 'Brand', fr_FR: 'Marque'}, image},
    {identifier: 'city', labels: {en_US: 'City'}, image},
    {identifier: 'color', labels: {}, image} as ReferenceEntity,
  ]);
};

const ReferenceEntityFetcher = {
  fetchAll,
};

export {ReferenceEntityFetcher};
