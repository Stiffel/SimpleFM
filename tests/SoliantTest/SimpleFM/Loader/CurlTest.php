<?php
namespace SoliantTest\SimpleFM\Loader;

use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\Exception\LoaderException;
use Soliant\SimpleFM\HostConnection;
use Soliant\SimpleFM\Loader\Curl as Loader;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-06-28 at 11:13:10.
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $params = ['hostname' => 'localhost', 'dbname' => 'testdb', 'username' => 'Admin', 'password' => ''];
        $hostConnection = new HostConnection(
            $params['hostname'],
            $params['dbname'],
            $params['username'],
            $params['password']
        );
        $this->adapter = new Adapter($hostConnection);
        $this->loader = new Loader();
        $this->adapter->setLoader($this->loader);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soliant\SimpleFM\Loader\Curl::load
     * @covers Soliant\SimpleFM\Loader\AbstractLoader::handleReturn
     * @covers Soliant\SimpleFM\Loader\AbstractLoader::errorCapture
     * @covers Soliant\SimpleFM\Loader\AbstractLoader::throwErrors
     * @covers Soliant\SimpleFM\StringUtils::extractErrorFromPhpMessage
     */
    public function testLoad()
    {
        $this->setExpectedException(LoaderException::class);
        $this->assertInstanceOf(Loader::class, $this->loader);
        $this->loader->throwErrors(true);
        $this->loader->load();
    }
}
