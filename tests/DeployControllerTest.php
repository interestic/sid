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
        $result = $this->post("http://sid.oscillo.interestic.com/deploy/{$env}");

        $this->assertEquals(
            $this->response->getContent(), "{$env} deploy start!"
        );
    }

    public function for_init_post()
    {
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

        $this->assertEquals($this->deploy->now, $this->deploy->mkdirJusttime());
    }

    /**
     * mksymlink test
     * @test
     */
    public function mkSymlink()
    {
        $this->assertTrue($this->deploy->mkSymlink());
    }

    /**
     * keepFiveDir test
     * @test
     */
    public function keepFiveDir()
    {
        $result = $this->deploy->keepFiveDir();
        if (count($result['dir_list']) > 5) {
            $this->assertEquals('deleted', $result['status']);
            $this->assertEquals($this->deploy->now, $result['dir_list'][5]);
        } else {
            $this->assertEquals('not deleted', $result['status']);
        }
    }

    /**
     * doDeploy test
     * @ test
     */
    public function doDeploy()
    {
        chdir('./storage/app/dev');
        $this->deploy->doDeploy();
    }

    /**
     * @test
     * @dataProvider for_payloadCheck_false
     */
    public function payloadCheck_fase($status){
        $payload_string_false = file_get_contents(base_path() . "/tests/payload_merge_{$status}.json");

        $result = $this->deploy->payloadCheck($payload_string_false);

        $this->assertTrue(!$result);
    }

    public function for_payloadCheck_false(){
        return [
            ['false'],
            ['blank']
        ];
    }

    /**
     * @test
     * @dataProvider for_payloadCheck_true
     */
    public function payloadCheck_true($env)
    {
        $payload_string_true = file_get_contents(base_path() . "/tests/payload_merge_true_{$env}.json");

        $result = $this->deploy->payloadCheck($payload_string_true);

        $this->assertEquals($env,$result);

    }

    public function for_payloadCheck_true(){
        return [
            ['dev'],
            ['stg'],
            ['prd']
        ];
    }
}
