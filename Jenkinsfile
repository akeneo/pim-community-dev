#!groovy

def editions = ["ce"]
def storages = ["orm", "odm"]
def phpVersion = "5.6"
def mysqlVersion = "5.5"
def esVersion = "none"
def features = "features"
def launchUnitTests = "yes"
def launchIntegrationTests = "yes"
def launchBehatTests = "yes"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests and code style checks', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
            choice(choices: 'yes\nno', description: 'Run behat tests', name: 'launchBehatTests'),
            string(defaultValue: 'odm,orm', description: 'Storage used for the behat tests (comma separated values)', name: 'storages'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the behat tests should run on (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            choice(choices: '5.6\n7.0\n7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
            choice(choices: '5.5\n5.7', description: 'Mysql version to run behat with', name: 'mysqlVersion'),
            choice(choices: 'none\n1.7\n5', description: 'ElasticSearch version to run behat with', name: 'esVersion')
        ])

        storages = userInput['storages'].tokenize(',')
        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
        phpVersion = userInput['phpVersion']
        mysqlVersion = userInput['mysqlVersion']
        esVersion = userInput['esVersion']
        launchUnitTests = userInput['launchUnitTests']
        launchIntegrationTests = userInput['launchIntegrationTests']
        launchBehatTests = userInput['launchBehatTests']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "pim_community_dev"

        if (editions.contains('ee') && 'yes' == launchBehatTests) {
           checkout([$class: 'GitSCM',
             branches: [[name: 'master']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           stash "pim_enterprise_dev"
        }
    }

    checkouts = [:];
    checkouts['community'] = {
        node('docker') {
            deleteDir()
            docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "app/console oro:requirejs:generate-config"
                sh "app/console assets:install"

                stash "pim_community_dev_full"
            }
            deleteDir()
        }
    }
    if (editions.contains('ee') && 'yes' == launchBehatTests) {
        checkouts['enterprise'] = {
            node('docker') {
                deleteDir()
                docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                    unstash "pim_enterprise_dev"

                    sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "app/console oro:requirejs:generate-config"
                    sh "app/console assets:install"

                    stash "pim_enterprise_dev_full"
                }
                deleteDir()
            }
        }
    }

    parallel checkouts
}

if (launchUnitTests.equals("yes")) {
    stage("Unit tests and Code style") {
        def tasks = [:]

        tasks["phpunit-5.6"] = {runPhpUnitTest("5.6")}
        tasks["phpunit-7.0"] = {runPhpUnitTest("7.0")}
        tasks["phpunit-7.1"] = {runPhpUnitTest("7.1")}

        tasks["phpspec-5.6"] = {runPhpSpecTest("5.6")}
        tasks["phpspec-7.0"] = {runPhpSpecTest("7.0")}
        tasks["phpspec-7.1"] = {runPhpSpecTest("7.1")}

        tasks["php-cs-fixer"] = {runPhpCsFixerTest()}

        tasks["php-coupling-detector"] = {runPhpCouplingDetectorTest()}

        tasks["grunt"] = {runGruntTest()}

        parallel tasks
    }
}

if (launchIntegrationTests.equals("yes")) {
    stage("Integration tests") {
        def tasks = [:]

        tasks["phpunit-5.6-orm-api-base"] = {runIntegrationTest("5.6", "orm", "PIM_Api_Base_Integration_Test")}
        tasks["phpunit-5.6-orm-api-controllers"] = {runIntegrationTest("5.6", "orm", "PIM_Api_Bundle_Controllers_Integration_Test")}
        tasks["phpunit-5.6-orm-api-controllers-catalog"] = {runIntegrationTest("5.6", "orm", "PIM_Api_Bundle_Controllers_Catalog_Integration_Test")}
        tasks["phpunit-5.6-orm-api-controller-product"] = {runIntegrationTest("5.6", "orm", "PIM_Api_Bundle_Controller_Product_Integration_Test")}
        tasks["phpunit-5.6-orm-catalog"] = {runIntegrationTest("5.6", "orm", "PIM_Catalog_Integration_Test")}
        tasks["phpunit-5.6-orm-completeness"] = {runIntegrationTest("5.6", "orm", "PIM_Catalog_Completeness_Integration_Test")}
        tasks["phpunit-5.6-orm-pqb"] = {runIntegrationTest("5.6", "orm", "PIM_Catalog_PQB_Integration_Test")}

        tasks["phpunit-7.0-orm-api-base"] = {runIntegrationTest("7.0", "orm", "PIM_Api_Base_Integration_Test")}
        tasks["phpunit-7.0-orm-api-controllers"] = {runIntegrationTest("7.0", "orm", "PIM_Api_Bundle_Controllers_Integration_Test")}
        tasks["phpunit-7.0-orm-api-controllers-catalog"] = {runIntegrationTest("7.0", "orm", "PIM_Api_Bundle_Controllers_Catalog_Integration_Test")}
        tasks["phpunit-7.0-orm-api-controller-product"] = {runIntegrationTest("7.0", "orm", "PIM_Api_Bundle_Controller_Product_Integration_Test")}
        tasks["phpunit-7.0-orm-catalog"] = {runIntegrationTest("7.0", "orm", "PIM_Catalog_Integration_Test")}
        tasks["phpunit-7.0-orm-completeness"] = {runIntegrationTest("7.0", "orm", "PIM_Catalog_Completeness_Integration_Test")}
        tasks["phpunit-7.0-orm-pqb"] = {runIntegrationTest("7.0", "orm", "PIM_Catalog_PQB_Integration_Test")}

        tasks["phpunit-7.1-orm-api-base"] = {runIntegrationTest("7.1", "orm", "PIM_Api_Base_Integration_Test")}
        tasks["phpunit-7.1-orm-api-controllers"] = {runIntegrationTest("7.1", "orm", "PIM_Api_Bundle_Controllers_Integration_Test")}
        tasks["phpunit-7.1-orm-api-controllers-catalog"] = {runIntegrationTest("7.1", "orm", "PIM_Api_Bundle_Controllers_Catalog_Integration_Test")}
        tasks["phpunit-7.1-orm-api-controller-product"] = {runIntegrationTest("7.1", "orm", "PIM_Api_Bundle_Controller_Product_Integration_Test")}
        tasks["phpunit-7.1-orm-catalog"] = {runIntegrationTest("7.1", "orm", "PIM_Catalog_Integration_Test")}
        // tasks["phpunit-7.1-orm-completeness"] = {runIntegrationTest("7.1", "orm", "PIM_Catalog_Completeness_Integration_Test")}
        tasks["phpunit-7.1-orm-pqb"] = {runIntegrationTest("7.1", "orm", "PIM_Catalog_PQB_Integration_Test")}

        // Temporarily deactivate integration tests with MongoDB because of stability issues
        // tasks["phpunit-5.6-odm"] = {runIntegrationTest("5.6", "odm")}
        // tasks["phpunit-7.0-odm"] = {runIntegrationTest("7.0", "odm")}
        // tasks["phpunit-7.1-odm"] = {runIntegrationTest("7.1", "odm")}

        parallel tasks
    }
}

if (launchBehatTests.equals("yes")) {
    stage("Functional tests") {
        def tasks = [:]

        if (editions.contains('ee') && storages.contains('odm')) {tasks["behat-ee-odm"] = {runBehatTest("ee", "odm", features, phpVersion, mysqlVersion, esVersion)}}
        if (editions.contains('ee') && storages.contains('orm')) {tasks["behat-ee-orm"] = {runBehatTest("ee", "orm", features, phpVersion, mysqlVersion, esVersion)}}
        if (editions.contains('ce') && storages.contains('odm')) {tasks["behat-ce-odm"] = {runBehatTest("ce", "odm", features, phpVersion, mysqlVersion, esVersion)}}
        if (editions.contains('ce') && storages.contains('orm')) {tasks["behat-ce-orm"] = {runBehatTest("ce", "orm", features, phpVersion, mysqlVersion, esVersion)}}

        parallel tasks
    }
}

def runGruntTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "pim_community_dev_full"
                sh "npm install"

                sh "grunt"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
            deleteDir()
        }
    }
}

def runPhpUnitTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update --ignore-platform-reqs alcaeus/mongo-php-adapter"
                }
                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"

                sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runIntegrationTest(phpVersion, storage, testSuiteName) {
    node('docker') {
        deleteDir()
        try {
            docker.image("mongo:2.4").withRun("--name mongodb", "--smallfiles") {
                docker.image("mysql:5.5").withRun("--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim", "--sql_mode=ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION") {
                    docker.image("carcel/php:${phpVersion}").inside("--link mysql:mysql --link mongodb:mongodb -v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                        unstash "pim_community_dev"

                        if (phpVersion != "5.6") {
                            sh "composer require --no-update --ignore-platform-reqs alcaeus/mongo-php-adapter"
                        }

                        sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                        sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                        sh "sed -i 's/database_host:     localhost/database_host:     mysql/' app/config/parameters_test.yml"

                        // Activate MongoDB if needed
                        if ('odm' == storage) {
                           sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
                           sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
                           sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
                           sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
                        }

                        sh "./app/console --env=test pim:install --force"

                        sh "mkdir -p app/build/logs/"
                        sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite ${testSuiteName} --log-junit app/build/logs/phpunit_integration.xml"
                    }
                }
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}-${storage}-${testSuiteName}] /\" app/build/logs/*.xml"

            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runPhpSpecTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update --ignore-platform-reqs alcaeus/mongo-php-adapter"
                }
                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"

                sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runPhpCsFixerTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:7.1").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer remove --dev --no-update doctrine/mongodb-odm-bundle;"
                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"

                sh "./bin/php-cs-fixer fix --diff --dry-run --format=junit --config=.php_cs.php > app/build/logs/phpcs.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-cs-fixer] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runBehatTest(edition, storage, features, phpVersion, mysqlVersion, esVersion) {
    node('docker') {
        deleteDir()
        try {
            tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
            fixtures = "PimInstallerBundle"

            sh "docker network create akeneo"

            if ('odm' == storage) {
                docker.image("mongo:2.4").run("--network akeneo", "--name mongodb", "--smallfiles")
            }

            if ('none' != esVersion) {
                docker.image("elasticsearch:${esVersion}").run("--network akeneo", "--name elasticsearch -e ES_JAVA_OPTS=\"-Xms256m -Xmx256m\"")
            }

            docker.image("mysql:${mysqlVersion}").run(
                "--network akeneo",
                "--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim",
                "--sql-mode=ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
            )

            docker.image("selenium/standalone-firefox:2.53.1-beryllium").run("--network akeneo", "--name selenium")

            docker.image("carcel/akeneo-behat:php-${phpVersion}").inside(
                "--network akeneo",
                "--name akeneo-behat -v /home/akeneo/.composer:/home/docker/.composer"
            ) {
                sh "mkdir symfony"

                dir('symfony') {
                    if ('ce' == edition) {
                        unstash "pim_community_dev_full"
                    } else {
                        unstash "pim_enterprise_dev_full"
                        dir('vendor/akeneo/pim-community-dev') {
                            deleteDir()
                            unstash "pim_community_dev"
                        }
                        tags = "${tags}&&~ce"
                        fixtures = "PimEnterpriseInstallerBundle"
                    }

                    // Configure the PIM
                    sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
                    sh "sed -i \"s#database_host: .*#database_host: mysql#g\" app/config/parameters_test.yml"
                    sh "printf \"    installer_data: '${fixtures}:minimal'\n\" >> app/config/parameters_test.yml"

                    // Activate MongoDB if needed
                    if ('odm' == storage) {
                       sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
                       sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
                       sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
                       sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
                    }

                    sh "php app/console --env=test pim:install --force"
                    sh "mkdir -p app/build/logs/behat"

                    sh "bin/behat --config behat.ci.yml --strict -v ${features} --tags '${tags}'"
                }
            }
        } finally {
            junit 'app/cache/behat/*.xml'
            archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
            sh "docker network rm \$(docker network ls -q) || true"
            sh "docker rm -f \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
        }
    }
}

def runPhpCouplingDetectorTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:7.1").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer remove --dev --no-update doctrine/mongodb-odm-bundle;"
                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "./bin/php-coupling-detector detect --config-file=.php_cd.php src"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"
            deleteDir()
        }
    }
}
