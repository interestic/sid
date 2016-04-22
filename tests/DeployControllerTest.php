<?php

use App\Http\Controllers\DeployController;
class DeployControllerTest extends TestCase
{

    public static $deploy_dir = null;
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->deploy = new DeployController();
        self::$deploy_dir = $this->deploy->deploy_dir;
    }

    /**
     * init test
     * @ test
     * @dataProvider for_init_post
     */
    public function init_post($env)
    {
        $this->post("/deploy/{$env}");

        $this->assertEquals(
            $this->response->getContent(), "{$env} deploy start!"
        );
    }

    public function for_init_post(){
        return [
            ['dev'],
            ['stg'],
            ['prd']
        ];
    }

    /**
     * mkdir test
     * @test
     */
    public function mkdirJusttime()
    {

        $this->assertEquals($this->deploy->now,$this->deploy->mkdirJusttime());
    }

    /**
     * mksymlink test
     * @test
     */
    public function mkSymlink(){
        $this->assertTrue($this->deploy->mkSymlink());
    }

    /**
     * keepFiveDir test
     * @test
     */
    public function keepFiveDir(){
        $result = $this->deploy->keepFiveDir();
        if(count($result['dir_list'])>5){
            $this->assertEquals('deleted',$result['status']);
            $this->assertEquals($this->deploy->now,$result['dir_list'][5]);
        }else{
            $this->assertEquals('not deleted',$result['status']);
        }
    }

    /**
     * doDeploy test
     * @test
     */
    public function doDeploy(){
        $this->deploy->doDeploy();
    }
}