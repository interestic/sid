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
    /usr/local/bin/composer install
@endtask