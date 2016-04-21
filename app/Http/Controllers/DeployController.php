<?php

namespace App\Http\Controllers;

use \Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public $deploy_dir = null;
    public $now = 0;
    public $env = null;

    public function __construct()
    {
        $this->env = 'dev';
        $this->now = date('YmdHis');
        $this->deploy_dir = $_ENV['DEPLOY_TARGET_DIR'];
    }

    public function init(Request $request, $env)
    {
        $this->env = $env;
        $this->deploy_dir = $this->deploy_dir.$this->env.'/';

        echo "{$this->env}: deploy start!\n";
        echo "{$this->env}: make dir.\n";
        $this->mkdirJusttime();
        echo "{$this->env}: mk symlink.\n";
        $this->mkSymlink();
        echo "{$this->env}: dir check.\n";
        $this->mkSymlink();
        echo "{$this->env}: dir check.\n";
        $this->mkSymlink();
    }

    public function mkdirJusttime()
    {
        $result = mkdir($this->deploy_dir.$this->env.'/' . $this->now);

        if ($result) {
            return $this->now;
        }

        return false;
    }

    public function mkSymlink()
    {
        if (is_dir($this->deploy_dir.$this->env.'/' . 'current')) {
            unlink($this->deploy_dir.$this->env.'/' . 'current');
        }

        $result = symlink($this->deploy_dir.$this->env.'/' . $this->now, $this->deploy_dir.$this->env.'/' . 'current');

        return $result;
    }

    public function keepFiveDir()
    {
        $list = scandir($this->deploy_dir.$this->env.'/');

        $cnt = 0;

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

    public function doDeploy()
    {
        $clone_dir = $this->deploy_dir.$this->env.'/' . $this->now;
        $envoy_command = '/vendor/bin/envoy run deploy';
        $deploy_command = "{$envoy_command} --env={$this->env} --clone_dir={$clone_dir}";

        $process = new Process(base_path() . $deploy_command);
        $process->setTimeout(3600);
        $process->setIdleTimeout(300);
        $process->setWorkingDirectory($this->now);
        echo "\n";
        $process->run(function ($type, $buffer) {
            echo $type;
            echo $buffer;
        });

    }

}
