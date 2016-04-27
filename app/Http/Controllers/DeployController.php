<?php

namespace App\Http\Controllers;

use \Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public $deploy_dir = null;
    public $now = 0;
    public $env = null;

    public function __construct()
    {
        chdir(base_path());
        $this->env = 'dev';
        $this->now = date('YmdHis');
        $this->deploy_dir = isset($_ENV['DEPLOY_TARGET_DIR'])?$_ENV['DEPLOY_TARGET_DIR']:'storage/app';
    }

    public function init($env='dev')
    {
        $this->env = $env;

        echo "{$this->env}: deploy start!\n";
        echo "{$this->env}: make dir.\n";
        $this->mkdirJusttime($this->env);
        echo "{$this->env}: mk symlink.\n";
        $this->mkSymlink($this->env);
        echo "{$this->env}: dir check.\n";
        $this->keepFiveDir($this->env);
        echo "{$this->env}: do deploy.\n";
        $this->doDeploy($this->env);
    }

    public function mkdirJusttime($env='dev')
    {
        $result = mkdir($this->deploy_dir.$env.'/' . $this->now);

        if ($result) {
            return $this->now;
        }

        return false;
    }

    public function mkSymlink($env='dev')
    {
        if (is_dir($this->deploy_dir.$env.'/' . 'current')) {
            unlink($this->deploy_dir.$env.'/' . 'current');
        }

        $result = symlink($this->deploy_dir.$env.'/' . $this->now, $this->deploy_dir.$env.'/' . 'current');

        return $result;
    }

    public function keepFiveDir($env='dev')
    {
        $list = scandir($this->deploy_dir.$env.'/');

        $cnt = 0;
        $dir_list =array();

        while ($cnt < count($list)) {
            if (!strstr($list[$cnt], '.') && $list[$cnt] != 'current') {
                $dir_list[] = $list[$cnt];
            }
            $cnt++;
        }

        if (5 < count($dir_list)) {
            $fs = new Filesystem();
            $fs->deleteDirectory($this->deploy_dir.$this->env.'/' . $dir_list[0]);

            return ['status' => 'deleted', 'dir_list' => $dir_list];
        };

        return ['status' => 'not deleted', 'dir_list' => $dir_list];
    }

    public function doDeploy($env='dev')
    {
        $clone_dir = $this->deploy_dir.$env.'/' . $this->now;
        $envoy_command = '/vendor/bin/envoy run deploy';
        $deploy_command = "{$envoy_command} --env={$env} --clone_dir={$clone_dir}";

        $this->out = null;
        $process = new Process(base_path() . $deploy_command);
        $process->setTimeout(3600);
        $process->setIdleTimeout(300);
        $process->setWorkingDirectory(base_path());
        echo "\n";
        $process->run(function ($type, $buffer) {
            $this->out[] = $type.$buffer."\n";
        });

        var_dump($this->out);
        return $this->out;

    }

}
