@setup
@endsetup

@servers(['web' => 'localhost'])

@macro('deploy')
    opening_ceremony
    download_source
    {{--setup_composer--}}
    copy_vendor
    copy_env
    migration_database
    exec_shared_gulp_and_static_copy
    endroll
    {{--composer_update--}}
@endmacro

@macro('shared_init')
    shared_init
@endmacro

@task('opening_ceremony')
    echo host connected!
    echo {{$env}} deploy started.
@endtask

@task('endroll')
echo {{$env}} deploy complete.
@endtask

@task('download_source')
    echo clone to env
    cd {{$clone_dir}}
    pwd
    wget --no-check-certificate https://github.com/interestic/oscillo/archive/{{$env}}.tar.gz
    echo download complete
    tar -xzf {{$env}}.tar.gz && rm -f {{$env}}.tar.gz && mv oscillo-{{$env}}/* ./ && rm -rf oscillo-{{$env}}
@endtask

@task('setup_composer')
    echo setup composer
    cd {{$clone_dir}}

    export COMPOSER_HOME=./
    php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
@endtask

@task('copy_vendor')
    echo copy vendor dir
    cd {{$clone_dir}}

    cp -rp ../../_shared/vendor ./
@endtask

@task('copy_env')
    echo copy .env file
    cd {{$clone_dir}}

    cp -rp ../../_shared/.env_{{$env}} ./.env
@endtask

@task('composer_install')
    echo composer install
    cd {{$clone_dir}}

    php composer.phar -v install
@endtask

@task('composer_update')
    echo composer update
    cd {{$clone_dir}}

    php composer.phar -v update
@endtask

@task('migration_database')
    echo migration Database
    cd {{$clone_dir}}

    php artisan migrate
@endtask

@task('exec_shared_gulp_and_static_copy')
    echo asset setup
    cd {{$clone_dir}}/../../_shared
    gulp
    cd {{$clone_dir}}/public
    cp -rp ../../../_shared/public/js ./
    cp -rp ../../../_shared/public/css ./
@endtask

@after
    @slack('https://hooks.slack.com/services/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', '#app', "$task run on [$env]")
@endafter