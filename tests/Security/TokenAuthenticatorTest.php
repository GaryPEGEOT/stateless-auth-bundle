<?php
/**
 * This file is part of the stateless-auth-bundle package.
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Tests\Security;

use Firebase\JWT\ExpiredException;
use GhostAgency\Bundle\StatelessAuthBundle\Encoder\EncoderInterface;
use GhostAgency\Bundle\StatelessAuthBundle\Security\TokenAuthenticator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;


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
        $token = new \stdClass();
        $token->username = 'bob';

        $this->encoder->expects($this->once())->method('decode')->willReturn($token);

        $this->assertEquals(['username' => 'bob'], $this->authenticator->getCredentials($this->getRequestMock()));
    }

    /**
     * @param \Exception $e     Exception to be thrown.
     * @param string     $level Log level.
     * @param string     $msg   Log message.
     * @param array      $ctx   Log context.
     *
     * @dataProvider provideExceptions
     */
    public function testGetCredentialsWithExpiredToken(\Exception $e, string $level, string $msg, array $ctx)
    {
        $this->encoder->expects($this->once())->method('decode')->willThrowException($e);
        $this->logger->expects($this->once())->method($level)->with($msg, $ctx);

        $this->assertNull($this->authenticator->getCredentials($this->getRequestMock())['username']);
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

        $this->assertTrue($this->authenticator->checkCredentials(['username' => 'coucou'], $user), 'Should be true.');
        $this->assertFalse($this->authenticator->checkCredentials([], $user), 'Should be false.');
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

    public function provideExceptions()
    {
        return [
            'Expired token' => [
                new ExpiredException('I\'m expired.'),
                'info',
                '[JWT Auth] {message}',
                ['message' => 'I\'m expired.'],
            ],
            'Forged token'  => [
                new \UnexpectedValueException('I\'m forged.'),
                'warning',
                '[JWT Auth] Unable to decode token {token} coming from {ip}: {reason}',
                ['token' => 'a.b.c', 'ip' => '127.0.0.1', 'reason' => 'I\'m forged.'],
            ],
        ];
    }

    protected function setUp()
    {
        $this->encoder       = $this->createMock(EncoderInterface::class);
        $this->logger        = $this->createMock(LoggerInterface::class);
        $this->authenticator = new TokenAuthenticator($this->encoder);
        $this->authenticator->setLogger($this->logger);
    }

    private function getRequestMock(): Request
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $req */
        $req = $this->createMock(Request::class);
        $req->headers = new AttributeBag();
        $req->headers->set('Authorization', 'Bearer a.b.c');
        $req->method('getClientIp')->willReturn('127.0.0.1');

        return $req;
    }
}
