#!groovy

stage('Build') {
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

    node {
        deleteDir()
        checkout scm

        if ('ce' != userInput['edition']) {
            stash "pim_community_dev"
        }

        // Install needed dependencies
        sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-dev"
        sh "app/console oro:requirejs:generate-config"

        stash "project_files"
    }
}

// Prepare all tests definition in advance to run them in parallel
def tasks = [:]

tasks['php-cs-fixer'] = {
    stage('php-cs-fixer') {
        parallel 'php-cs-fixer-with-php-5.4': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.4').inside {
                    unstash "project_files"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2"
                }
            }
        },
        'php-cs-fixer-with-php-5.5': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.5').inside {
                    unstash "project_files"
                    sh "composer global require friendsofphp/php-cs-fixer ^1.12"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix features --dry-run -v --diff --level=psr2"
                    sh "/home/docker/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src --dry-run -v --diff --level=psr2"
                }
            }
        },
        'php-cs-fixer-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
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

tasks['phpunit'] = {
    stage('phpunit') {
        parallel 'phpunit-with-php-5.4': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.4').inside {
                    unstash "project_files"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
            }
        },
        'phpunit-with-php-5.5': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.5').inside {
                    unstash "project_files"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
            }
        },
        'phpunit-with-php-5.6': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.6').inside {
                    unstash "project_files"
                    sh "composer global require phpunit/phpunit 3.7.*"
                    sh "/home/docker/.composer/vendor/phpunit/phpunit/composer/bin/phpunit -c app/phpunit.jenkins.xml --testsuite PIM_Unit_Test"
                }
            }
        }
    }
}

tasks['phpspec'] = {
    stage('phpspec') {
        parallel 'phpspec-with-php-5.4': {
            node('docker') {
                deleteDir()
                docker.image('carcel/php:5.4').inside {
                    unstash "project_files"
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
                    unstash "project_files"
                    sh "composer global require phpspec/phpspec 2.1.*"
                    sh "composer global require akeneo/phpspec-skip-example-extension 1.1.*"
                    sh "cp app/config/parameters.yml app/config/parameters_test.yml"
                    sh "/home/docker/.composer/vendor/phpspec/phpspec/bin/phpspec run --no-interaction --format=dot"
                }
            }
        },
        'phpspec-with-php-5.6': {
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

tasks['functional_tests'] = {
    stage('behat') {
        node {
            deleteDir()

            if ('ce' == userInput['edition']) {
                unstash "project_files"

                tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
            } else {
                // Checkout pim-enterprise-dev
                checkout([$class:            'GitSCM',
                          branches:          [[name: '1.4']],
                          userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
                ])

                // Install dependencies then unstash the CE PR branch directly into the vendors
                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                dir('vendor/akeneo/pim-community-dev') {
                    deleteDir()
                    unstash "pim_community_dev"
                }

                tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
            }

            // Create mysql hostname (MySQL docker container name)
            mysqlHostName = "mysql_akeneo_job_pim-community-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}"

            // Configure the PIM
            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
            if ('ce' == userInput['edition']) {
                sh "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            } else {
                sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            }

            // Activate MongoDB if needed
            if ('odm' == userInput['storage']) {
                sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
                sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
                sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
                sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat"
            sh "mkdir -p app/build/logs/consumer"
            sh "mkdir -p app/build/screenshots"

            sh "cp behat.ci.yml behat.yml"

            sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE} ${env.BUILD_NUMBER} ${userInput['storage']} ${userInput['features']} akeneo/job/pim-community-dev/job/${env.JOB_BASE_NAME} 3 5.6 5.5 \"${tags}\""

            step([$class: 'ArtifactArchiver', allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log', defaultExcludes: false, excludes: null])
            step([$class: 'JUnitResultArchiver', testResults: 'app/build/logs/behat/*.xml'])
        }
    }
}

parallel tasks
