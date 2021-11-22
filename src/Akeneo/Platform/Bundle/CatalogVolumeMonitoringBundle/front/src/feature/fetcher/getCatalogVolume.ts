import {transformVolumesToAxis} from './catalogVolumeWrapper';

const getCatalogVolume = async (route: any) => {
  const response = await fetch(route);

  const catalogVolume = await response.json();

  return transformVolumesToAxis(catalogVolume);
};

export {getCatalogVolume};
