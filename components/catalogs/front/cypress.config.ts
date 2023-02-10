import {defineConfig} from 'cypress';
import {addCucumberPreprocessorPlugin} from '@badeball/cypress-cucumber-preprocessor';
import createBundler from '@bahmutov/cypress-esbuild-preprocessor';
import createEsbuildPlugin from '@badeball/cypress-cucumber-preprocessor/esbuild';

export default defineConfig({
    e2e: {
        specPattern: '**/*.feature',
        setupNodeEvents: async (on, config) => {
            await addCucumberPreprocessorPlugin(on, config);

            on(
                'file:preprocessor',
                createBundler({
                    target: 'chrome109',
                    plugins: [createEsbuildPlugin(config)],
                }),
            );

            return config;
        },
    },
});
