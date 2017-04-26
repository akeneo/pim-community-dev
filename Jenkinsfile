#!groovy

def editions = ["ce"]
def storages = ["orm", "odm"]
def phpVersion = "5.6"
def mysqlVersion = "5.5"
def esVersion = "5"
def features = "features"
def launchUnitTests = "yes"
def launchIntegrationTests = "yes"
def launchBehatTests = "yes"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        //userInput = input(message: 'Launch tests?', parameters: [
        //    choice(choices: 'yes\nno', description: 'Run unit tests and code style checks', name: 'launchUnitTests'),
        //    choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
        //    choice(choices: 'yes\nno', description: 'Run behat tests', name: 'launchBehatTests'),
        //    string(defaultValue: 'odm,orm', description: 'Storage used for the behat tests (comma separated values)', name: 'storages'),
        //    string(defaultValue: 'ee,ce', description: 'PIM edition the behat tests should run on (comma separated values)', name: 'editions'),
        //    string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
        //    choice(choices: '5.6\n7.0\n7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
        //    choice(choices: '5.5\n5.7', description: 'Mysql version to run behat with', name: 'mysqlVersion'),
        //    choice(choices: '5\n1.7', description: 'ElasticSearch version to run behat with', name: 'esVersion')
        //])

        storages = ["orm"]
        editions = ["ce"]
        features = "features/channel/create_channel.feature,features/channel/delete_channel.feature"
        launchUnitTests = "no"
        launchIntegrationTests = "no"
        launchBehatTests = "yes"

        //storages = userInput['storages'].tokenize(',')
        //editions = userInput['editions'].tokenize(',')
        //features = userInput['features']
        //phpVersion = userInput['phpVersion']
        //mysqlVersion = userInput['mysqlVersion']
        //esVersion = userInput['esVersion']
        //launchUnitTests = userInput['launchUnitTests']
        //launchIntegrationTests = userInput['launchIntegrationTests']
        //launchBehatTests = userInput['launchBehatTests']
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
            //cleanUpEnvironment()
            docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/docker/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "app/console oro:requirejs:generate-config"
                sh "app/console assets:install"

                stash "pim_community_dev_full"
            }
            //cleanUpEnvironment()
            deleteDir()
        }
    }

    if (editions.contains('ee') && 'yes' == launchBehatTests) {
        checkouts['enterprise'] = {
            node('docker') {
                //cleanUpEnvironment()
                docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/docker/.composer") {
                    unstash "pim_enterprise_dev"

                    sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "app/console oro:requirejs:generate-config"
                    sh "app/console assets:install"

                    stash "pim_enterprise_dev_full"
                }
                //cleanUpEnvironment()
                deleteDir()
            }
        }
    }

    echo "DEBUG_BEHAT_1"

    parallel checkouts
}
echo "toto1"
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
echo "toto2"
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
echo "toto3"
if (launchBehatTests.equals("yes")) {
    echo "DEBUG_BEHAT_2"
    stage("Functional tests") {
        def tasks = [:]
        def paths = features.split(' *, *')

        echo "DEBUG_BEHAT_3"

        for(int i = 0; i < paths.size(); i++) {
            for(int j = 0; j < editions.size(); j++) {
                for(int k = 0; k < storages.size(); k++) {
                    echo "trololo"
                    node('docker') {
                        docker.image("carcel/php:5.6").inside() {
                            tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
                            if ('ce' == editions[j]) {
                                unstash "pim_community_dev_full"
                            } else {
                                unstash "pim_enterprise_dev_full"
                                dir('vendor/akeneo/pim-community-dev') {
                                    deleteDir()
                                    unstash "pim_community_dev"
                                }
                                tags = "${tags}&&~ce"
                                sh "cp vendor/akeneo/pim-community-dev/bin/behat-list bin/"
                            }

                            tags = sh returnStdout: true, script: "bin/behat-list \"${paths[i]}\" \"${tags}\""
                            tags = tags.split('\r?\n')
                        }
                    }

                    for(int l = 0; l < tags.size(); l++) {
                        tasks["behat-${editions[j]}-${storages[k]}-${paths[i]}-${tags[l]}"] = runBehatTest(editions[j], storages[k], paths[i], tags[l], phpVersion, mysqlVersion, esVersion)
                    }
                }
            }
        }

        parallel tasks
    }
}

def runGruntTest() {
    node('kubernetes-docker') {
        cleanUpEnvironment()
        try {
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "pim_community_dev_full"
                sh "npm install"

                sh "grunt"
            }
        } finally {
            cleanUpEnvironment()
        }
    }
}

def runPhpUnitTest(phpVersion) {
    node('kubernetes-docker') {
        cleanUpEnvironment()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/docker/.composer") {
                unstash "pim_community_dev"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update --ignore-platform-reqs alcaeus/mongo-php-adapter"
                }
                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"

                sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            cleanUpEnvironment()
        }
    }
}

def runIntegrationTest(phpVersion, storage, testSuiteName) {
    node('kubernetes-docker') {
        cleanUpEnvironment()
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
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}-${storage}-${testSuiteName}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            cleanUpEnvironment()
        }
    }
}

def runPhpSpecTest(phpVersion) {
    node('kubernetes-docker') {
        cleanUpEnvironment()
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
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            cleanUpEnvironment()
        }
    }
}

def runPhpCsFixerTest() {
    node('kubernetes-docker') {
        cleanUpEnvironment()

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
            cleanUpEnvironment()
        }
    }
}

def runPhpCouplingDetectorTest() {
    node('kubernetes-docker') {
        cleanUpEnvironment()

        try {
            docker.image("carcel/php:7.1").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer remove --dev --no-update doctrine/mongodb-odm-bundle;"
                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "./bin/php-coupling-detector detect --config-file=.php_cd.php src"
            }
        } finally {
            cleanUpEnvironment()
        }
    }
}

def cleanUpEnvironment() {
    deleteDir()
    sh '''
        docker ps -a -q | xargs -n 1 -P 8 -I {} docker rm -f {} > /dev/null
        docker volume ls -q | xargs -n 1 -P 8 -I {} docker volume rm {} > /dev/null
        docker network ls --filter name=akeneo -q | xargs -n 1 -P 8 -I {} docker network rm {} > /dev/null
    '''
}

def runBehatTest(edition, storage, path, batch, phpVersion, mysqlVersion, esVersion) {
    return {
        node('kubernetes-docker') {
            cleanUpEnvironment()

            def workspace = "/home/docker/symfony"
            sh "docker network create akeneo"

            sh "docker pull mongo:2.4"
            sh "docker pull elasticsearch:${esVersion}"
            sh "docker pull mysql:${mysqlVersion}"
            sh "docker pull selenium/standalone-firefox:2.53.1-beryllium"
            sh "docker pull carcel/akeneo-behat:php-${phpVersion}"

            sh "docker run -d --network akeneo --name mongodb mongo:2.4 --smallfiles"
            sh "docker run -d --network akeneo --name elasticsearch -e ES_JAVA_OPTS=\"-Xms256m -Xmx256m\" elasticsearch:${esVersion}"
            sh "docker run -d --network akeneo --name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim mysql:${mysqlVersion} --sql-mode=ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
            sh "docker run -d --network akeneo --name selenium selenium/standalone-firefox:2.53.1-beryllium"
            sh "docker run -d --network akeneo --name akeneo-behat -e WORKSPACE=${workspace} -v /home/akeneo/.composer:/home/docker/.composer -v \$(pwd):${workspace} -w ${workspace} carcel/akeneo-behat:php-${phpVersion}"

            try {
                if ('ce' == edition) {
                    unstash "pim_community_dev_full"
                } else {
                    unstash "pim_enterprise_dev_full"
                    dir('vendor/akeneo/pim-community-dev') {
                        deleteDir()
                        unstash "pim_community_dev"
                    }
                }

                // Configure the PIM
                sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
                sh "sed -i \"s#database_host: .*#database_host: mysql#g\" app/config/parameters_test.yml"
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

                //if ('ce' == edition) {
                //    sh "docker exec akeneo-behat bin/behat --config behat.ci.yml --strict -v ${features} --tags '~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app'"
                //} else {
                //    sh "docker exec akeneo-behat bin/behat --config behat.ci.yml --strict -v ${features} --tags '~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce'"
                //}

                // Install PIM
                sh "docker exec akeneo-behat php app/console --env=test pim:install --force"
                sh "docker exec akeneo-behat bin/behat-list '${path}' '${tags}' > /dev/null"
                sh "docker exec akeneo-behat bin/behat --format 'progress, Pim\\Behat\\Formatter\\JUnitFormatter' --out 'null,app/logs/' --config behat.ci.yml --strict -v --tags '@${batch}' ${path}"
            } finally {
                junit 'app/logs/*.xml'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
                cleanUpEnvironment()
            }
        }
    }
}
