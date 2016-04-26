@setup
@endsetup

@servers(['web' => 'localhost'])

@macro('deploy')
    opening_ceremony
    clone_env
    oscillo_setup
@endmacro

@macro('shared_init')
    shared_init
@endmacro

@task('opening_ceremony')
    echo host connected!
    echo {{$env}} deploy started.
@endtask

@task('clone_env')
    echo clone to env
    cd {{$clone_dir}}
    pwd
    wget --no-check-certificate https://github.com/interestic/oscillo/archive/{{$env}}.tar.gz
    echo download complete
@endtask

@task('oscillo_setup')
    echo setup oscillo
    cd {{$clone_dir}}
    tar -xzf {{$env}}.tar.gz && rm -f {{$env}}.tar.gz && mv oscillo-dev/* ./ && rm -rf oscillo-dev
    export COMPOSER_HOME=./
    php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    php composer.phar -v install
@endtask