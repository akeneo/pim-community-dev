const {execSync} = require('child_process');

const extensions = ['ts', 'tsx', 'php', 'yml', 'js', 'feature', 'json', 'md'];
const forbiden = [
  '.git',
  '/vendor/',
  '/node_modules/',
  '/archive/',
  '/cache/',
  '/web/',
  '/logs/',
  '.DS_Store',
  'tests/legacy/',
  'var/file_storage/',
  'generate.js',
  'coverage/src',
  '.png',
  '.jpeg',
  '.jpg',
  '.JPG',
  '.PNG',
  '.JPEG',
  '.ico',
  '.gz',
  '.xlsx',
];
const replaces = {
  enriched_entity: 'reference_entity',
  enriched_entities: 'reference_entities',
  'enriched-entity': 'reference-entity',
  'enriched-entities': 'reference-entities',
  enrichedentity: 'referenceentity',
  enrichedentities: 'referenceentities',
  EnrichedEntity: 'ReferenceEntity',
  EnrichedEntities: 'ReferenceEntities',
  enrichedEntity: 'referenceEntity',
  enrichedEntities: 'referenceEntities',
  'enriched\\ entity': 'reference\\ entity',
  'enriched\\ entities': 'reference\\ entities',
  ENRICHED_ENTITY: 'REFERENCE_ENTITY',
  ENRICHED_ENTITIES: 'REFERENCE_ENTITIES',
  'Enriched\\ Entity': 'Reference\\ Entity',
  'Enriched\\ Entities': 'Reference\\ Entities',
  'Enriched\\ Entities': 'Reference\\ Entities',
  ENRICHED_ENTITY: 'REFERENCE_ENTITY',
  ENRICHED_ENTITIES: 'REFERENCE_ENTITIES',
};

const fileNames = execSync('find . -type f')
  .toString('utf8')
  .split('\n');
fileNames.pop();

const filteredFilenames = fileNames.filter(fileName => !forbiden.some(oldName => -1 !== fileName.indexOf(oldName)));
const oldNames = Object.keys(replaces);

filteredFilenames.forEach(fileName => {
  console.log(fileName);
  oldNames.forEach(oldValue => execSync(`sed -i '' s/${oldValue}/${replaces[oldValue]}/g ${fileName}`));
});

filteredFilenames
  .filter(fileName => oldNames.some(oldName => -1 !== fileName.indexOf(oldName)))
  .map(fileName => [
    fileName,
    oldNames.reduce((result, current) => result.replace(new RegExp(current, 'g'), replaces[current]), fileName),
  ])
  .forEach(([fromName, toName]) => {
    execSync(`mkdir -p $(dirname ${toName}) && git mv ${fromName} ${toName}`);
  });
