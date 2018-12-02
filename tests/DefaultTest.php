<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

/**
 * Class SecurityControllerTest
 * @package App\Tests
 */
class DefaultTest extends WebTestCase
{
    protected static $application;
    protected static $ct;

    public function __construct()
    {
        self::$ct = self::$kernel->getContainer();
    }

    protected function setUp(): void
    {
        // self::runCommand('doctrine:database:create');
        // self::runCommand('doctrine:schema:update --force --dump-sql');

    }

    protected static function runCommand($command): int
    {
        $command = sprintf('%s --quiet', $command);
        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication(): Application
    {
        if (null === self::$application) {
            $client = static::createClient();
            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }
        return self::$application;
    }

    public function testGetRoutes(): void
    {
        $this->assertGetRoutes(true);
    }

    /**
     * @param bool $dump : if test fails, the last route dumped is causing the error
     */
    public function assertGetRoutes(bool $dump = false): void
    {
        $method = 'GET';
        $router = self::$ct->get('router');
        $routes = $router->getRouteCollection()->all();
        $client = static::createClient();
        foreach ($routes as $route) {
            if ($route->getPath() == '/resetpassword/{token}' || !in_array($method, $route->getMethods())) continue;
            $output = '';
            if ($dump) {
                foreach ($route->getMethods() as $mtd) {
                    $output .= $mtd . ' ';
                }
                var_dump($output . $route->getPath());
            }
            $crawler = $client->request($method, str_replace('{id}', '1', $route->getPath()));
            $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [200, 301, 302, 303, 304, 307, 308]));
        }
    }
}