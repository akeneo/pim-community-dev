import fs from 'fs';
import path from 'path';

const findSourceFiles = (dir: string): string[] => {
    let matches: string[] = [];
    let files = fs.readdirSync(dir);

    files.forEach(file => {
        let filepath = path.join(dir, file);
        let stat = fs.lstatSync(filepath);

        if (stat.isDirectory()) {
            matches.push(...findSourceFiles(filepath));
            return;
        }

        // Skip files in __mocks__ directories
        if (filepath.match(/__mocks__/)) {
            return;
        }

        // Skip typescript definition files
        if (filepath.match(/\.d\.ts$/)) {
            return;
        }

        // Skip test files
        if (filepath.match(/\.test\./)) {
            return;
        }

        matches.push(filepath);
    });

    return matches;
};

export default (dir: string) => findSourceFiles(dir).forEach(file => jest.mock(file));
