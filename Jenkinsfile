#!groovy

def editions = ["ce"]
def storages = ["orm", "odm"]
def features = "features"
def launchUnitTests = "yes"
def launchIntegrationTests = "yes"
def launchBehatTests = "yes"

Integer nbAvailableNode = 8
def testFilesCE = []

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests and code style checks', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
            choice(choices: 'yes\nno', description: 'Run behat tests', name: 'launchBehatTests'),
            string(defaultValue: 'odm,orm', description: 'Storage used for the behat tests (comma separated values)', name: 'storages'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the behat tests should run on (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features')
        ])

        storages = userInput['storages'].tokenize(',')
        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
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
             branches: [[name: '1.7']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           stash "pim_enterprise_dev"
        }
    }

    checkouts = [:];
    checkouts['community'] = {
        node('docker') {
            deleteDir()
            docker.image("akeneo/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "app/console oro:requirejs:generate-config"
                sh "app/console assets:install"

                stash "pim_community_dev_full"
            }

            unstash "pim_community_dev_full"

            def output = sh (
                returnStdout: true,
                script: 'find src tests -name "*Integration.php" -exec sh -c "grep -Ho \'function test\' {} | uniq -c"  \\; | sed "s/:function test//"'
            )
            def files = output.tokenize('\n')
            for (file in files) {
                def fileInfo = file.tokenize(' ')
                testFilesCE += ["nbTests": fileInfo[0] as Integer , "path": fileInfo[1]]
            }

            deleteDir()
        }
    }
    if (editions.contains('ee') && 'yes' == launchBehatTests) {
        checkouts['enterprise'] = {
            node('docker') {
                deleteDir()
                docker.image("akeneo/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
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

        tasks["phpunit"] = {runPhpUnitTest()}
        tasks["phpspec"] = {runPhpSpecTest()}
        tasks["php-cs-fixer"] = {runPhpCsFixerTest()}
        tasks["grunt"] = {runGruntTest()}

        parallel tasks
    }
}

if (launchIntegrationTests.equals("yes")) {
    stage("Integration tests") {
        def tasks = buildIntegrationTestTasks('orm', nbAvailableNode, testFilesCE)
        parallel tasks
    }
}

if (launchBehatTests.equals("yes")) {
    stage("Functional tests") {
        def tasks = [:]

        if (editions.contains('ee') && storages.contains('odm')) {tasks["behat-ee-odm"] = {runBehatTest("ee", "odm", features)}}
        if (editions.contains('ee') && storages.contains('orm')) {tasks["behat-ee-orm"] = {runBehatTest("ee", "orm", features)}}
        if (editions.contains('ce') && storages.contains('odm')) {tasks["behat-ce-odm"] = {runBehatTest("ce", "odm", features)}}
        if (editions.contains('ce') && storages.contains('orm')) {tasks["behat-ce-orm"] = {runBehatTest("ce", "orm", features)}}

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

def runPhpUnitTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:5.6").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "find app/build/logs -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[phpunit] /\""
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

/**
 * Builds a list of tasks to run integration tests on multiple nodes in parallel.
 * Each nodes should run approximately the same number of tests.
 *
 * @param storage         storage of the PIM ('orm' or 'odm')
 * @param nbAvailableNode number of available nodes to execute the integration tests
 * @param testFiles       list of file containing the filepath of the file and the number of integration tests in the file
 *                        [
 *                            ["path" : "filePath1", "nbTests" : 10],
 *                            ["path" : "filePath2", "nbTests" : 25]
 *                        ]
 *
 * @return list of tasks to execute in parallel
 */
def buildIntegrationTestTasks(String storage, Integer nbAvailableNode, def testFiles) {
    def tasks = [:]

    def filesPerNode = getTestFilesPerNode(nbAvailableNode, testFiles)

    Integer nodeId = 1
    for (files in filesPerNode) {
        def listFiles = files

        tasks["integration-${nodeId}"] = {runIntegrationTest(storage, listFiles)}
        nodeId++;
    }

    return tasks
}

void runIntegrationTest(String storage, def testFiles) {
    node('docker') {
        deleteDir()
        sh "docker stop \$(docker ps -a -q) || true"
        sh "docker rm \$(docker ps -a -q) || true"

        try {
            docker.image("mongo:2.4").withRun("--name mongodb", "--smallfiles") {
                docker.image("mysql:5.5").withRun("--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim --tmpfs=/var/lib/mysql/:rw,noexec,nosuid,size=1000m --tmpfs=/tmp/:rw,noexec,nosuid,size=300m") {
                    docker.image("akeneo/php:5.6").inside("--link mysql:mysql --link mongodb:mongodb") {
                        unstash "pim_community_dev_full"


                        sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                        sh "sed -i 's/database_host:     localhost/database_host:     mysql/' app/config/parameters_test.yml"
                        sh "sed -i \"s@installer_data:    PimInstallerBundle:minimal@installer_data: '%kernel.root_dir%/../features/Context/catalog/footwear'@\" app/config/parameters_test.yml"

                        // Activate MongoDB if needed
                        if ('odm' == storage) {
                           sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
                           sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
                           sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
                           sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
                        }

                        sh "./app/console --env=test pim:install --force"

                        sh "mkdir -p app/build/logs/"

                        String testSuiteFiles = ""
                        for (testFile in testFiles) {
                            testSuiteFiles += "<file>../${testFile}</file>"
                        }

                        sh "sed -i \"s#<file></file>#${testSuiteFiles}#\" app/phpunit.xml.dist"

                        sh "php -d error_reporting='E_ALL' ./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Integration_Test --log-junit app/build/logs/phpunit_integration.xml"
                    }
                }
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[integration] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

/**
 * Calculate and return a list of files to execute in each node, in order to execute approximately the same
 * number of integration tests per node.
 *
 * @param nbNode number of available nodes to execute the integration tests
 * @param files   list of file containing the filepath of the file and the number of integration tests in the file
 *                [
 *                    ["path" : "filePath1", "nbTests" : 10],
 *                    ["path" : "filePath2", "nbTests" : 25]
 *                ]
 *
 * @return an array, each entry being a list of filepath to execute in a node.
 *         [
 *             ["filePath1", "filePath2"]
 *             ["filePath3", "filePath4"]
 *         ]
 */
def getTestFilesPerNode(Integer nbNode, def files) {
    def filesPerNode = []

    Integer nbIntegrationTests = 0;
    for (file in files) {
        nbIntegrationTests += file["nbTests"]
    }
    Integer nbTestsPerNode = nbIntegrationTests.intdiv(nbNode) + 1

    Integer nodeTestCounter = 0
    def nodeFiles = []

    for (file in files) {
        nodeTestCounter += file["nbTests"]
        nodeFiles += file["path"]

        if (nodeTestCounter > nbTestsPerNode) {
            filesPerNode << nodeFiles

            nodeFiles = []
            nodeTestCounter = 0
        }
    }

    if (nodeTestCounter > 0) {
        filesPerNode << nodeFiles
    }

    return filesPerNode
}

def runPhpSpecTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:5.6").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "find app/build/logs/ -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[phpspec] /\""
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

def runPhpCsFixerTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:5.6").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./bin/php-cs-fixer fix --diff --dry-run --format=junit --config=.php_cs.php > app/build/logs/phpcs.xml"
            }
        } finally {
            sh "find app/build/logs/ -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-cs-fixer] /\""
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runBehatTest(edition, storage, features) {
    node() {
        dir("behat-${edition}-${storage}") {
            deleteDir()
            if ('ce' == edition) {
               unstash "pim_community_dev_full"
               tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
            } else {
                unstash "pim_enterprise_dev_full"
                dir('vendor/akeneo/pim-community-dev') {
                    deleteDir()
                    unstash "pim_community_dev"
                }
                tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
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

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"

            try {
                sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${edition}-${storage} ${env.BUILD_NUMBER} ${storage} ${features} ${env.JOB_NAME} 5 5.6 5.5 \"${tags}\" \"behat-${edition}-${storage}\" --exit_on_failure"
            } finally {
                sh "find app/build/logs/behat/ -name \"*.xml\" | xargs sed -i \"s/ name=\\\"/ name=\\\"[${edition}-${storage}] /\""
                junit 'app/build/logs/behat/*.xml'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
                deleteDir()
            }
        }
    }
}
