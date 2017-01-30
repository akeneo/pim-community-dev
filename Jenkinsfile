#!groovy

def editions = ["ee", "ce"]
def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def automaticBranches = ["1.4", "1.5", "1.6", "master"]
def behatAttempts = 5
def php_version = "5.6"
def mysql_version = "5.5"

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

        storages = [userInput['storage']]
        editions = [userInput['edition']]
        features = userInput['features']
        php_version = userInput['php_version']
        mysql_version = userInput['mysql_version']
    }

    node {
        deleteDir()

        checkout scm
        sh "composer update --ignore-platform-reqs --no-scripts --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"
        stash "pim_community_dev"

        if (editions.contains('ee')) {
           checkout([$class: 'GitSCM',
             branches: [[name: 'master']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           sh "composer update --ignore-platform-reqs --no-scripts --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"
           stash "pim_enterprise_dev"
        }
    }

    node('docker') {
        deleteDir()
        docker.image('carcel/php:5.6').inside {
            unstash "pim_community_dev"
            sh "composer run-script post-update-cmd"
            sh "app/console oro:requirejs:generate-config"
            stash "pim_community_dev"
        }
    }

    if (editions.contains('ee')) {
        node('docker') {
            deleteDir()
            docker.image('carcel/php:5.6').inside {
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

tasks['php-cs-fixer'] = {
    stage('php-cs-fixer') {

        def fixers = [
            '-concat_without_spaces',
            '-empty_return',
            '-multiline_array_trailing_comma',
            '-phpdoc_short_description',
            '-single_quote',
            '-trim_array_spaces',
            '-operators_spaces',
            '-unary_operators_spaces',
            '-unalign_double_arrow',
            'align_double_arrow',
            'newline_after_open_tag',
            'ordered_use',
            'phpdoc_order'
        ]

        parallel 'php-cs-fixer-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "pim_community_dev"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
                }
            }
        },
        'php-cs-fixer-with-php-7.0': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.0').inside {
                    unstash "pim_community_dev"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
                }
            }
        },
        'php-cs-fixer-with-php-7.1': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.1').inside {
                    unstash "pim_community_dev"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2 --fixers=" + fixers.join(',')
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
                unstash "pim_community_dev"
                sh "npm install"
                sh "grunt codestyle --force"
            }
        }
    }
}

tasks['phpunit'] = {
    stage('phpunit') {
        parallel 'phpunit-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
            }
        },
        'phpunit-with-php-7.0': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.0').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
            }
        },
        'phpunit-with-php-7.1': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:7.1').inside {
                    unstash "pim_community_dev"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
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
                    unstash "pim_community_dev"
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
                    unstash "pim_community_dev"
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
                    unstash "pim_community_dev"
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
                unstash "pim_community_dev"
                sh "npm install"
                sh "grunt test --force"
            }
        }
    }
}

tasks["behat"] = {
    node {
        for (storage in storages) {
            for (edition in editions) {
                stage("behat-${edition}-${storage}") {
                    deleteDir()

                    if ('ce' == edition) {
                       unstash "pim_community_dev"

                       tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
                    } else {
                        unstash "pim_enterprise_dev"
                        dir('vendor/akeneo/pim-community-dev') {
                            deleteDir()
                            unstash "pim_community_dev"
                        }

                        tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
                    }

                    // Create mysql hostname (MySQL docker container name)
                    mysqlHostName = "mysql_akeneo_job_pim-community-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}_behat-${edition}-${storage}"

                    // Configure the PIM
                    sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
                    sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
                    if ('ce' == edition) {
                       sh "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
                    } else {
                       sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
                    }

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
                    sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE} ${env.BUILD_NUMBER} ${storage} ${features} akeneo/job/pim-community-dev/job/${env.JOB_BASE_NAME} ${behatAttempts} ${php_version} ${mysql_version} \"${tags}\" \"behat-${edition}-${storage}\""

                    archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log'
                    junit 'app/build/logs/behat/*.xml'
                }
            }
        }
    }
}

parallel tasks
