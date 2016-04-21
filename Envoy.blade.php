@servers(['web' => 'localhost'])

@macro('deploy')
    opening_ceremony
    shared_fetch
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

@task('shared_init')
    echo shared_init started.
    cd storage/app/_shared
    git clone git@bitbucket.org:oscillo/oscillo.git ./
    echo ===current branch list
    git branch -r
    git fetch --prune
    echo ===current branch
    git branch -a |grep \*
    git fetch --prune && git checkout -t origin/stg
    echo === changed branch
    git branch -a |grep \*
    git fetch --prune && git checkout -t origin/prd
    echo === changed branch
    git branch -a |grep \*
    echo ===
@endtask

@task('shared_fetch')
    echo _shared repo fetch.
    cd {{$clone_dir}}
    cd ../../_shared
    git fetch --prune
    echo ===current branch list
    git branch -r
    echo ===current branch
    git branch -a |grep \*
    git fetch --prune && git checkout origin/{{$env}}
    echo === changed branch
    git branch -a |grep \*
    echo ===
    cd {{$clone_dir}}
@endtask

@task('clone_env')
    echo clone to env
    cd {{$clone_dir}}
    echo clone from _shared
    git clone ../../_shared ./
@endtask

@task('oscillo_setup')
    echo setup oscillo
    cd {{$clone_dir}}
    composer install
@endtask