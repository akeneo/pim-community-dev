#!groovy

def editions = ["ee", "ce"]
def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def automaticBranches = ["1.4", "1.5", "1.6", "master", "PR-5560"]
def behatAttempts = 5

stage('build') {
    if (!automaticBranches.contains(env.BRANCH_NAME)) {
        userInput = input(message: 'Launch tests?', parameters: [
            [
                $class: 'ChoiceParameterDefinition',
                name: 'storage',
                choices: 'odm\norm',
                description: 'Storage used for the build, MongoDB (default) or MySQL'
            ],
            [
                $class: 'ChoiceParameterDefinition',
                name: 'edition',
                choices: 'ee\nce',
                description: 'Run behat tests on EE or CE edition'
            ],
            [
                $class: 'TextParameterDefinition',
                name: 'features',
                defaultValue: 'features,vendor/akeneo/pim-community-dev/features',
                description: 'Behat scenarios to build'
            ]
        ])

        storages = [userInput['storage']]
        editions = [userInput['edition']]
        features = userInput['features']
    }

    node {
        deleteDir()

        checkout scm
        sh "composer update --ignore-platform-reqs --no-scripts --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"
        sh "composer require alcaeus/mongo-php-adapter --ignore-platform-reqs"
        stash "pim_community_dev"

        if (editions.contains('ee')) {
           checkout([$class: 'GitSCM',
             branches: [[name: '1.5']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           sh "composer update --ignore-platform-reqs --no-scripts --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"
           sh "composer require alcaeus/mongo-php-adapter --ignore-platform-reqs"
           stash "pim_enterprise_dev"
        }
    }

    node('docker') {
        deleteDir()
        docker.image('carcel/php:5.4').inside {
            unstash "pim_community_dev"
            sh "composer run-script post-update-cmd"
            sh "app/console oro:requirejs:generate-config"
            stash "pim_community_dev"
        }
    }

    if (editions.contains('ee')) {
        node('docker') {
            deleteDir()
            docker.image('carcel/php:5.4').inside {
                unstash "pim_enterprise_dev"
                sh "composer run-script post-update-cmd"
                sh "app/console oro:requirejs:generate-config"
                stash "pim_enterprise_dev"
            }
        }
    }
}

// Prepare all tests definition in advance to run them in parallel
def tasks = [:]

tasks['phpspec'] = {
    stage('phpspec') {
        parallel 'phpspec-with-php-5.4': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.4').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot"
                }
            }
        },
        'phpspec-with-php-5.5': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.5').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot spec"
                }
            }
        },
        'phpspec-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot spec"
                }
            }
        },
        'phpspec-with-php-7.0': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.0').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot spec"
                }
            }
        }
    }
}

parallel tasks
