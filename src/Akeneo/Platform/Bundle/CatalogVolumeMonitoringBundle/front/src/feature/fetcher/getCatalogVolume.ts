import {transformVolumesToKeyFigures} from './catalogVolumeWrapper';

const getCatalogVolume = async (route: any) => {
  const response = await fetch(route);

  const catalogVolume = await response.json();

  return transformVolumesToKeyFigures(catalogVolume);
};

export {getCatalogVolume};
