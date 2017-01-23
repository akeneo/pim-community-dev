#!groovy

def ceBranch = "dev-master"
def ceOwner = "akeneo"
def features = "features,vendor/akeneo/pim-community-dev/features"
def automaticBranches = ["1.4", "1.5", "1.6", "master"]
def storages = ["orm", "odm"]
def behatAttempts = 5

stage('build') {
    if (!automaticBranches.contains(env.BRANCH_NAME)) {
        userInput = input(message: 'Launch tests?', parameters: [
            [
                $class: 'TextParameterDefinition',
                name: 'ce_branch',
                defaultValue: '1.4.x-dev',
                description: 'Community Edition branch used for the build (ONLY if you created a CE branch for this PR, let blank otherwise)'
            ],
            [
                $class: 'TextParameterDefinition',
                name: 'ce_owner',
                defaultValue: 'akeneo',
                description: 'Owner of the repository on GitHub (ONLY if you work from a forked repository AND you specified a "ce_branch")'
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
                name: 'php_version',
                choices: '5.6\n7.0\n7.1',
                description: 'PHP version to run behat with'
            ],
            [
                $class: 'ChoiceParameterDefinition',
                name: 'mysql_version',
                choices: '5.5\n5.7',
                description: 'MySQL version to run behat with'
            ]
        ])

        ceBranch = userInput['ce_branch']
        ceOwner = userInput['ce_owner']
        features = userInput['features']
        storages = [userInput['storage']]
    }

    node {
        deleteDir()
        checkout scm

        sh "composer require --no-update \"${ceOwner}/pim-community-dev\":\"${ceBranch}\""

        // Install needed dependencies
        sh "composer update --ignore-platform-reqs --no-scripts --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"

        stash "project_files"
    }

    node('docker') {
        deleteDir()
        docker.image('carcel/php:5.4').inside {
            unstash "project_files"
            sh "composer run-script post-update-cmd"
            sh "app/console oro:requirejs:generate-config"
            stash "project_files"
        }
    }
}

// Prepare all tests definition in advance to run them in parallel
def tasks = [:]

tasks['php-cs-fixer'] = {
    stage('php-cs-fixer') {
        parallel 'php-cs-fixer-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "project_files"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2"
                }
            }
        },
        'php-cs-fixer-with-php-7.0': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.0').inside {
                    unstash "project_files"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2"
                }
            }
        },
        'php-cs-fixer-with-php-7.1': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.1').inside {
                    unstash "project_files"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2"
                }
            }
        }
    }
}

tasks['grunt-codestyle'] = {
    stage('grunt codestyle') {
        node('docker') {
            deleteDir()
            docker.image('akeneo_grunt').inside {
                unstash "project_files"
                sh "npm install"
                sh "grunt codestyle --force"
            }
        }
    }
}

tasks['phpspec'] = {
    stage('phpspec') {
        parallel 'phpspec-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "project_files"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot"
                }
            }
        },
        'phpspec-with-php-7.0': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.0').inside {
                    unstash "project_files"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot"
                }
            }
        },
        'phpspec-with-php-7.1': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.1').inside {
                    unstash "project_files"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot"
                }
            }
        }
    }
}

tasks['jasmine'] = {
    stage('jasmine') {
        node('docker') {
            deleteDir()
            docker.image('akeneo_grunt').inside {
                unstash "project_files"
                sh "npm install"
                sh "grunt test --force"
            }
        }
    }
}

tasks["behat"] = {
    node {
        for (storage in storages) {
            stage("behat-${storage}") {
                deleteDir()
                unstash "project_files"

                tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"

                // Create mysql hostname (MySQL docker container name)
                mysqlHostName = "mysql_akeneo_job_pim-enterprise-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}_behat-${storage}"

                // Configure the PIM
                sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
                sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
                sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"

                // Activate MongoDB if needed
                if ('odm' == storage) {
                    sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
                    sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
                    sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
                    sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
                }

                sh "mkdir -p app/build/logs/behat"
                sh "mkdir -p app/build/logs/consumer"
                sh "mkdir -p app/build/screenshots"

                sh "cp behat.ci.yml behat.yml"
                sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE} ${env.BUILD_NUMBER} ${storage} ${features} akeneo/job/pim-enterprise-dev/job/${env.JOB_BASE_NAME} ${behatAttempts} ${php_version} ${mysql_version} \"${tags}\" \"behat-${storage}\""

                stash "project_files"
            }
        }
    }
}

parallel tasks

node {
    unstash "project_files"
    archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log'
    junit 'app/build/logs/behat/*.xml'
}
