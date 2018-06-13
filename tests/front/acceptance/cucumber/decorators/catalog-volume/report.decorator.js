const Header = require('../common/header.decorator');
const Volume = require('./volume.decorator');

const Report = async (nodeElement, createElementDecorator, parent) => {
  const children = {
    'Header':  {
      selector: '.AknTitleContainer',
      decorator: Header
    },
    'Volume': {
      selector: '.AknCatalogVolume-axis',
      decorator: Volume,
      multiple: true
    }
  };

  const getChildren = createElementDecorator(children);
  const getHeader = async () => await getChildren(parent, 'Header');

  const getVolumeByType = async (typeName) => {
    let volumes = await Promise.all(await getChildren(parent, 'Volume'));

    volumes = await Promise.all(volumes.map(async (volume) => {
      return { type: await volume.getType(), volume };
    }));

    return volumes.filter(volume => volume.type === typeName)[0].volume;
  };

  return { getHeader, getVolumeByType };
};

module.exports = Report;
