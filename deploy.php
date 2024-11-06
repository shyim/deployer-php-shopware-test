<?php

namespace Deployer;

require_once 'recipe/common.php';
require_once 'contrib/cachetool.php';

set('bin/console', '{{bin/php}} {{release_or_current_path}}/bin/console');

set('cachetool', '/run/php/php-fpm.sock');
set('application', 'Shopware 6');
set('allow_anonymous_stats', false);
set('default_timeout', 3600); // Increase when tasks take longer than that.

// These files are shared among all releases.
set('shared_files', [
    '.env.local',
    'install.lock',
    'public/.htaccess',
    'public/.user.ini',
]);

// These directories are shared among all releases.
set('shared_dirs', [
    'config/jwt',
    'files',
    'var/log',
    'public/media',
    'public/thumbnail',
    'public/sitemap',
]);

// These directories are made writable (the definition of "writable" requires attention).
// Please note that the files in `config/jwt/*` receive special attention in the `sw:writable:jwt` task.
set('writable_dirs', [
    'config/jwt',
    'custom/plugins',
    'files',
    'public/bundles',
    'public/css',
    'public/fonts',
    'public/js',
    'public/media',
    'public/sitemap',
    'public/theme',
    'public/thumbnail',
    'var',
]);

task('sw:deployment:helper', static function() {
   run('cd {{release_path}} && ./vendor/bin/shopware-deployment-helper run');
});



desc('Deploys your project');
task('deploy', [
    'deploy:prepare',
    'deploy:clear_paths',
    'sw:deployment:helper',
    'deploy:publish',
]);

task('deploy:update_code')->setCallback(static function () {
    upload('.', '{{release_path}}', [
        'options' => [
            '--exclude=.git',
            '--exclude=deploy.php',
            '--exclude=node_modules',
        ],
    ]);
});

// Hosts

host('157.90.147.236')
    ->setLabels([
        'type' => 'web',
        'env'  => 'production',
    ])
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/')
    ->set('http_user', 'www-data');

// Hooks

after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'cachetool:clear:opcache');
