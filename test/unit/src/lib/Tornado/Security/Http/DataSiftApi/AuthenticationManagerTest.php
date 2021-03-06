<?php

namespace Test\Tornado\Security\Http\DataSiftApi;

use \Mockery;

use Symfony\Component\HttpFoundation\ParameterBag;

use DataSift\Http\Request;

use Tornado\Organization\Organization;
use Tornado\Organization\Agency;
use Tornado\Organization\Brand;
use Tornado\Security\Http\DataSiftApi\AuthenticationManager;

/**
 * AuthenticationManager
 *
 * LICENSE: This software is the intellectual property of MediaSift Ltd.,
 * and is covered by retained intellectual property rights, including
 * copyright. Distribution of this software is strictly forbidden under
 * the terms of this license.
 *
 * @category           Applications
 * @package            \Test\Tornado\Security\Http\DataSiftApi
 * @copyright          2015-2016 MediaSift Ltd.
 * @license            http://mediasift.com/licenses/internal MediaSift Internal License
 * @link               https://github.com/datasift/tornado
 *
 * @coversDefaultClass Tornado\Security\Http\DataSiftApi\AuthenticationManager
 */
class AuthenticationManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testAuthUnlessNoAuthHeaderExists()
    {
        $mocks = $this->getMocks();
        $mocks['request'] = $this->getRequest([]);

        $manager = $this->getManager($mocks);
        $manager->auth($mocks['request']);
    }

    /**
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testAuthUnlessAuthDataGivenWithInvalidFormat()
    {
        $mocks = $this->getMocks();
        $mocks['request'] = $this->getRequest([
            'auth' => 'stringwithoutcolon'
        ]);

        $manager = $this->getManager($mocks);
        $manager->auth($mocks['request']);
    }

    /**
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     * @covers ::authByBrand
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testAuthByBrandUnlessAgencyNotFound()
    {
        $mocks = $this->getMocks();
        $mocks['request'] = $this->getRequest([
            'auth' => $mocks['username'] . ':' . $mocks['apiKey']
        ]);
        $mocks['agencyRepository']->shouldReceive('findOne')
            ->once()
            ->with(['datasift_username' => $mocks['username']])
            ->andReturnNull();

        $manager = $this->getManager($mocks);
        $manager->auth($mocks['request']);
    }

    /**
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     * @covers ::authByBrand
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testAuthByBrandUnlessBrandNotFound()
    {
        $mocks = $this->getMocks();
        $mocks['request'] = $this->getRequest([
                'auth' => $mocks['username'] . ':' . $mocks['apiKey'],
        ]);
        $mocks['agencyRepository']->shouldReceive('findOne')
            ->once()
            ->with(['datasift_username' => $mocks['username']])
            ->andReturn($mocks['agency']);
        $mocks['brandRepository']->shouldReceive('findOne')
            ->once()
            ->with(['agency_id' => $mocks['agencyId'], 'datasift_apikey' => $mocks['apiKey']])
            ->andReturnNull();

        $manager = $this->getManager($mocks);
        $manager->auth($mocks['request']);
    }

    /**
     * DataProvider for testAuthByBrand
     *
     * @return array
     */
    public function authByBrandProvider()
    {
        return [
            'Happy path' => [
                'mocks' => $this->getMocks()
            ],
            'Org not allowed path' => [
                'mocks' => $this->getMocks(false),
                'expectedException' => '\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException'
            ]
        ];
    }

    /**
     * @dataProvider authByBrandProvider
     *
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     * @covers ::authByBrand
     *
     * @param array $mocks
     * @param string $expectedException
     */
    public function testAuthByBrand(array $mocks, $expectedException = '')
    {
        $mocks['request'] = $this->getRequest([
            'auth' => $mocks['username'] . ':' . $mocks['apiKey']
        ]);
        $mocks['agencyRepository']->shouldReceive('findOne')
            ->once()
            ->with(['datasift_username' => $mocks['username']])
            ->andReturn($mocks['agency']);
        $mocks['brandRepository']->shouldReceive('findOne')
            ->once()
            ->with(['agency_id' => $mocks['agencyId'], 'datasift_apikey' => $mocks['apiKey']])
            ->andReturn($mocks['brand']);

        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $manager = $this->getManager($mocks);
        $res = $manager->auth($mocks['request']);

        $this->assertInstanceOf(Brand::class, $res);
        $this->assertSame($mocks['agency'], $mocks['request']->attributes->get('agency'));
        $this->assertSame($mocks['brand'], $mocks['request']->attributes->get('brand'));
    }

    /**
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     * @covers ::authByAgency
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testAuthByAgencyUnlessAgencyNotFound()
    {
        $mocks = $this->getMocks();
        $mocks['request'] = $this->getRequest([
            'auth' => $mocks['username'] . ':' . $mocks['apiKey']
        ]);
        $mocks['agencyRepository']->shouldReceive('findOne')
            ->once()
            ->with(['datasift_username' => $mocks['username'], 'datasift_apikey' => $mocks['apiKey']])
            ->andReturnNull();

        $manager = $this->getManager($mocks);
        $manager->auth($mocks['request'], AuthenticationManager::TYPE_AGENCY);
    }

    /**
     * DataProvider for testAuthByAgency
     *
     * @return array
     */
    public function authByAgencyProvider()
    {
        return [
            'Happy path' => [
                'mocks' => $this->getMocks()
            ],
            'Org not allowed path' => [
                'mocks' => $this->getMocks(false),
                'expectedException' => '\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException'
            ]
        ];
    }

    /**
     * @dataProvider authByAgencyProvider
     *
     * @covers ::__construct
     * @covers ::auth
     * @covers ::extractCredentials
     * @covers ::authByAgency
     *
     * @param array $mocks
     * @param string $expectedException
     */
    public function testAuthByAgency(array $mocks, $expectedException = '')
    {
        $mocks['request'] = $this->getRequest([
            'auth' => $mocks['username'] . ':' . $mocks['apiKey']
        ]);
        $mocks['agencyRepository']->shouldReceive('findOne')
            ->with(['datasift_username' => $mocks['username'], 'datasift_apikey' => $mocks['apiKey']])
            ->andReturn($mocks['agency']);

        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $manager = $this->getManager($mocks);
        $res = $manager->auth($mocks['request'], AuthenticationManager::TYPE_AGENCY);

        $this->assertInstanceOf(Agency::class, $res);
        $this->assertSame($mocks['agency'], $mocks['request']->attributes->get('agency'));
        $this->assertNull($mocks['request']->attributes->get('brand'));
    }

    /**
     * DataProvider for testAuth
     *
     * @return array
     */
    public function authProvider()
    {
        return [
            'Basic Auth' => [
                'request' => $this->getRequest([
                   'auth' => 'Basic ' . base64_encode('dave:bob')
                ]),
                'type' => AuthenticationManager::TYPE_AGENCY,
                'credentials' => [
                    'username' => 'dave',
                    'api_key' => 'bob'
                ]
            ],
            'Basic Authorization' => [
                'request' => $this->getRequest([
                   'authorization' => 'Basic ' . base64_encode('dave:bob')
                ]),
                'type' => AuthenticationManager::TYPE_AGENCY,
                'credentials' => [
                    'username' => 'dave',
                    'api_key' => 'bob'
                ]
            ],
            'Basic Authorisation' => [
                'request' => $this->getRequest([
                   'authorisation' => 'Basic ' . base64_encode('dave:bob')
                ]),
                'type' => AuthenticationManager::TYPE_AGENCY,
                'credentials' => [
                    'username' => 'dave',
                    'api_key' => 'bob'
                ]
            ],
            'Auth' => [
                'request' => $this->getRequest([
                   'auth' => 'dave:bob'
                ]),
                'type' => AuthenticationManager::TYPE_AGENCY,
                'credentials' => [
                    'username' => 'dave',
                    'api_key' => 'bob'
                ]
            ],
            'Query Auth' => [
                'request' => $this->getRequest(
                    [],
                    [
                        'username' => 'dave',
                        'api_key' => 'bob'
                    ]
                ),
                'type' => AuthenticationManager::TYPE_AGENCY,
                'credentials' => [
                    'username' => 'dave',
                    'api_key' => 'bob'
                ]
            ],
        ];
    }

    /**
     * @see https://jiradatasift.atlassian.net/browse/NEV-424
     *
     * @dataProvider authProvider
     *
     * @covers ::auth
     * @covers ::extractCredentials
     *
     * @param \DataSift\Http\Request $request
     * @param string $type
     * @param array $credentials
     * @param string $expectedException
     */
    public function testAuth(Request $request, $type, array $credentials, $expectedException = '')
    {
        $organizationRepository = Mockery::mock('Tornado\Organization\Organization\DataMapper');
        $agencyRepository = Mockery::mock('Tornado\Organization\Agency\DataMapper');
        $brandRepository = Mockery::mock('Tornado\Organization\Brand\DataMapper');

        $authByMethod = ($type == AuthenticationManager::TYPE_AGENCY) ? 'authByAgency' : 'authByBrand';
        $manager = Mockery::Mock(
            'Tornado\Security\Http\DataSiftApi\AuthenticationManager',
            [$organizationRepository, $agencyRepository, $brandRepository]
        )->makePartial()->shouldAllowMockingProtectedMethods();

        $expected = 'Result';
        $manager->shouldReceive($authByMethod)
                ->with($request, $credentials)
                ->andReturn($expected);

        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->assertEquals($expected, $manager->auth($request, $type));
    }

    /**
     * @return array
     */
    protected function getMocks($orgHasApiPermission = true)
    {

        $agencyRepository = Mockery::mock('Tornado\Organization\Agency\DataMapper');
        $brandRepository = Mockery::mock('Tornado\Organization\Brand\DataMapper');
        $request = Mockery::mock(Request::class);

        $username = 'username';
        $apiKey = 'apiKey';

        $organizationId = 2;
        $organization = Mockery::mock('Tornado\Organization\Organization');
        $organizationRepository = Mockery::mock('Tornado\Organization\Organization\DataMapper');
        $organizationRepository->shouldReceive('findOne')
            ->with(['id' => $organizationId])
            ->atLeast(0)
            ->andReturn($organization);
        $organization->shouldReceive('hasPermission')
            ->with('api')
            ->andReturn($orgHasApiPermission);

        $agencyId = 1;
        $agency = new Agency();
        $agency->setId($agencyId);
        $agency->setOrganizationId($organizationId);
        $agency->setDatasiftUsername($username);
        $agency->setDatasiftApiKey($apiKey);

        $brand = new Brand();
        $brand->setAgencyId($agencyId);

        return [
            'organizationRepository' => $organizationRepository,
            'agencyRepository' => $agencyRepository,
            'brandRepository' => $brandRepository,
            'organization' => $organization,
            'organizationId' => $organizationId,
            'agency' => $agency,
            'agencyId' => $agencyId,
            'brand' => $brand,
            'username' => $username,
            'apiKey' => $apiKey,
            'request' => $request
        ];
    }

    /**
     * @param array $mocks
     *
     * @return \Tornado\Security\Http\DataSiftApi\AuthenticationManager
     */
    protected function getManager(array $mocks)
    {
        return new AuthenticationManager(
            $mocks['organizationRepository'],
            $mocks['agencyRepository'],
            $mocks['brandRepository']
        );
    }

    /**
     * Gets a Request object for testing
     *
     * @param array $headers
     *
     * @return \DataSift\Http\Request
     */
    protected function getRequest(array $headers, array $query = [], array $attributes = [])
    {
        $request = new Request();
        $request->headers = new ParameterBag($headers);
        $request->query = new ParameterBag($query);
        $request->attributes = new ParameterBag($attributes);
        return $request;
    }
}
