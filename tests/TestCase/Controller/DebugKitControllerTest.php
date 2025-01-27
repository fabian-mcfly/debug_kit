<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.19.1
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Test\TestCase\Controller;

use Authorization\AuthorizationService;
use Authorization\Policy\OrmResolver;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use DebugKit\Controller\DebugKitController;
use DebugKit\TestApp\Application;

/**
 * Composer controller test.
 */
class DebugKitControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * tests `debug` is disabled
     *
     * @return void
     */
    public function testDebugDisabled()
    {
        Configure::write('debug', false);

        $this->configApplication(Application::class, []);

        $this->get('/debug-kit/toolbar/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
        $this->assertResponseError();
        $this->assertResponseContains('Error page');
    }

    /**
     * Build controller with AuthorizationService
     * in request attribute
     *
     * @return DebugKit\Controller\DebugKitController
     */
    private function _buildController()
    {
        $request = new ServerRequest(['url' => '/debug-kit/']);

        $resolver = new OrmResolver();
        $authorization = new AuthorizationService($resolver);

        $request = $request->withAttribute('authorization', $authorization);

        return new DebugKitController($request, new Response());
    }

    /**
     * tests authorization is enabled but not ignored
     *
     * @return void
     */
    public function testDontIgnoreAuthorization()
    {
        $controller = $this->_buildController();
        $event = new Event('testing');
        $controller->beforeFilter($event);

        $this->assertFalse($controller->getRequest()->getAttribute('authorization')->authorizationChecked());
    }

    /**
     * tests authorization is checked to avoid
     * AuthorizationRequiredException throwned
     *
     * @return void
     */
    public function testIgnoreAuthorization()
    {
        Configure::write('DebugKit.ignoreAuthorization', true);

        $controller = $this->_buildController();
        $event = new Event('testing');
        $controller->beforeFilter($event);

        $this->assertTrue($controller->getRequest()->getAttribute('authorization')->authorizationChecked());
    }
}
