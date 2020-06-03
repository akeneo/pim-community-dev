const path = require('path');
const fs = require('fs');
const {compile} = require('json-schema-to-typescript');
const $RefParser = require('json-schema-ref-parser');

const JSON_SCHEMA_EXT = '.schema.json';
const TYPESCRIPT_EXT = '.ts';

const args = process.argv.slice(2);
const relative_filename = path.relative(process.cwd(), __filename);

const help = () => {
  console.log(`
Convert a given JSON Schema file (or several) to generate their respective TypeScript interface.

Usage: node ${relative_filename} <source> <target>

where <source> is either a filepath or a directory 
where <target> is a directory

if <source> is a directory, all *.schema.json files inside will be handled.

The given source(s) will be copied into the target directory AND then compiled
to typescript interface(s).

eg: 
node ${relative_filename} schemas/product.schema.json models/
node ${relative_filename} schemas/ models/
  `);
};

const error = message => {
  console.error(`
${message}

See: node ${relative_filename} --help
  `);
};

if (args.indexOf('--help') !== -1) {
  help();
  process.exit(1);
}

if (args.length !== 2) {
  error('The command expects 2 args');
  process.exit(1);
}

const source = args[0];
const target = args[1];

if (!fs.existsSync(source)) {
  error(`The source ${source} does not exists.`);
  process.exit(1);
}

if (fs.existsSync(source) && !fs.lstatSync(source).isDirectory() && !source.endsWith(JSON_SCHEMA_EXT)) {
  error(`The source ${source} must be a directory or a JSON schema.`);
  process.exit(1);
}

if (fs.existsSync(target) && !fs.lstatSync(target).isDirectory()) {
  error(`The target ${target} is not a directory.`);
  process.exit(1);
}

const resolveSchema = async (jsonSchemaPath, cwd) => {
  let schema = fs.readFileSync(jsonSchemaPath, {encoding: 'utf8'});
  schema = JSON.parse(schema);

  // Resolve $ref links into flatten schemas
  schema = await $RefParser.dereference(cwd, schema, {});

  return schema;
};

const copyAndCompile = async (jsonSchemaSource, jsonSchemaTarget) => {
  const typescriptFilepath = jsonSchemaTarget.replace(JSON_SCHEMA_EXT, TYPESCRIPT_EXT);

  // We assume that the root directory for relative schemas is the directory
  // where the schema is stored.
  const cwd = path.dirname(jsonSchemaSource) + path.sep;

  const name = path.basename(jsonSchemaSource);
  const schema = await resolveSchema(jsonSchemaSource, cwd);
  const typescript = await compile(schema, name, {cwd});

  fs.writeFileSync(jsonSchemaTarget, JSON.stringify(schema, null, 2));
  fs.writeFileSync(typescriptFilepath, typescript);
};

const recursiveLookup = async (source, target) => {
  if (fs.lstatSync(source).isDirectory()) {
    const children = fs.readdirSync(source);

    for (let child of children) {
      let childPath = path.join(source, child);

      if (fs.lstatSync(childPath).isDirectory()) {
        let childTarget = path.join(target, child);
        if (!fs.existsSync(childTarget)) {
          fs.mkdirSync(childTarget);
        }

        await recursiveLookup(childPath, childTarget);
        continue;
      }

      if (!child.endsWith(JSON_SCHEMA_EXT)) {
        continue;
      }

      let jsonSchemaSource = path.join(source, child);
      let jsonSchemaTarget = path.join(target, child);

      await copyAndCompile(jsonSchemaSource, jsonSchemaTarget);
    }
  } else {
    let schemaFilename = path.basename(source);
    let jsonSchemaSource = source;
    let jsonSchemaTarget = path.join(target, schemaFilename);

    await copyAndCompile(jsonSchemaSource, jsonSchemaTarget);
  }
};

(async () => {
  if (!fs.existsSync(target)) {
    fs.mkdirSync(target);
  }

  await recursiveLookup(source, target);
})();
