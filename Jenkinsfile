#!groovy

stage('Prepare build') {
    node {
        step([$class: 'GitHubSetCommitStatusBuilder'])

        deleteDir()
        checkout scm

        sh "mkdir -p app/build/api"
        sh "mkdir -p app/build/code-browser"
        sh "mkdir -p app/build/coverage"
        sh "mkdir -p app/build/coverage-parts"
        sh "mkdir -p app/build/logs/phpspec"
        sh "mkdir -p app/build/logs/behat"
        sh "mkdir -p app/build/logs/phpdoc"
        sh "mkdir -p app/build/logs/consumer"
        sh "mkdir -p app/build/screenshots"
        sh "mkdir -p app/build/pdepend"
        sh "mkdir -p app/build/phpdox"

        stash "project_files"
    }
}

stage('Acceptance Tests') {
    userInput = input(message: 'Launch acceptance tests?', parameters: [
        [
            $class: 'TextParameterDefinition',
            name: 'ce_branch',
            defaultValue: '',
            description: 'Community Edition branch used for the build (ONLY if you created a CE branch for this PR, let blank otherwise)'
        ],
        [
            $class: 'ChoiceParameterDefinition',
            name: 'storage',
            choices: 'odm\norm',
            description: 'Storage used for the build, MongoDB (default) or MySQL'
        ],
        [
            $class: 'TextParameterDefinition',
            name: 'features',
            defaultValue: 'features,vendor/akeneo/pim-community-dev/features',
            description: 'Behat scenarios to build'
        ],
        [
            $class: 'ChoiceParameterDefinition',
            name: 'attempts',
            choices: '3\n1\n2\n4\n5'
        ],
        [
            $class: 'ChoiceParameterDefinition',
            name: 'php_version',
            choices: '5.6\n7.0',
            description: 'PHP version to run the tests with'
        ],
        [
            $class: 'ChoiceParameterDefinition',
            name: 'mysql_version',
            choices: '5.5\n5.7',
            description: 'MySQL version to run the tests with'
        ]
    ])

    node {
        unstash "project_files"

        // Set composer.json
        if ('' != userInput['ce_branch']) {
            sh "composer require --no-update \"akeneo/pim-community-dev\":\"dev-${userInput['ce_branch']}\""
        }
        if ('5.6' != userInput['php_version']) {
            sh "composer require --no-update \"alcaeus/mongo-php-adapter\":\"1.0.*\""
        }

        // Configure the PIM
        sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
        sh "sed -i \"s#database_host: .*#database_host: mysql_pim_enterprise_dev_${env.BUILD_NUMBER}#g\" app/config/parameters_test.yml"
        sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"

        // Activate MongoDB if needed
        if ('odm' == userInput['storage']) {
            sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
            sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
            sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
            sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
        }

        // Install the vendors and launch the DCI build
        sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs"
        sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build -p ${userInput['php_version']} -m ${userInput['mysql_version']} ${env.WORKSPACE} ${env.BUILD_NUMBER} pim-enterprise-dev ${userInput['storage']} ${userInput['features']} akeneo/job/pim-enterprise-dev/view/Pull%20Requests/job/${env.JOB_BASE_NAME} ${userInput['attempts']}"
    }
}

stage('Results') {
    node {
        step([$class: 'ArtifactArchiver', allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log', defaultExcludes: false, excludes: null])
        step([$class: 'JUnitResultArchiver', testResults: 'app/build/logs/behat/*.xml'])
        step([$class: 'GitHubCommitStatusSetter', resultOnFailure: 'FAILURE', statusMessage: [content: 'Build finished']])
    }
}
