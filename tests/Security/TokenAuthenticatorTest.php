<?php
/**
 * This file is part of the stateless-auth-bundle package.
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Tests\Security;

use GhostAgency\Bundle\StatelessAuthBundle\Encoder\EncoderInterface;
use GhostAgency\Bundle\StatelessAuthBundle\Security\TokenAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticatorTest extends TestCase
{
    /**
     * @var EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encoder;

    /**
     * @var TokenAuthenticator
     */
    private $authenticator;


    public function testStart()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);
        $res = $this->authenticator->start($req);

        $this->assertEquals('{"error":"Authentication required"}', $res->getContent());
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $res->getStatusCode());
    }

    public function testSupports()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);
        $req->headers = new AttributeBag();

        $this->assertFalse($this->authenticator->supports($req), 'No support');
        $req->headers->set('Authorization', 'Bearer a.b.c');

        $this->assertTrue($this->authenticator->supports($req), 'Support');
    }

    public function testGetCredentials()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);
        $req->headers = new AttributeBag();
        $req->headers->set('Authorization', 'Bearer a.b.c');

        $token = new \stdClass();
        $token->username = 'bob';

        $this->encoder->expects($this->once())->method('decode')->willReturn($token);

        $this->assertEquals(['username' => 'bob'], $this->authenticator->getCredentials($req));
    }

    public function testGetUser()
    {
        $user = $this->createMock(UserInterface::class);

        /** @var UserProviderInterface|\PHPUnit_Framework_MockObject_MockObject $provider */
        $provider = $this->createMock(UserProviderInterface::class);
        $provider->expects($this->once())->method('loadUserByUsername')->willReturn($user);

        $this->assertSame($user, $this->authenticator->getUser(['username' => 'wesh_poto'], $provider));
    }

    public function testCheckCredentials()
    {
        /** @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(UserInterface::class);

        $this->assertTrue($this->authenticator->checkCredentials(['coucou'], $user), 'Should be true.');
    }

    public function testOnAuthenticationFailure()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);

        $res = $this->authenticator->onAuthenticationFailure($req, new AuthenticationException());

        $this->assertEquals(Response::HTTP_FORBIDDEN, $res->getStatusCode());
    }

    public function testOnAuthenticationSuccess()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);

        $this->assertNull($this->authenticator->onAuthenticationSuccess($req, $token, 'main'));
    }

    public function testSupportsRememberMe()
    {
        $this->assertFalse($this->authenticator->supportsRememberMe(), 'Should definitely not support it.');
    }

    protected function setUp()
    {
        $this->encoder = $this->createMock(EncoderInterface::class);
        $this->authenticator = new TokenAuthenticator($this->encoder);
    }
}
