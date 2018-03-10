<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Security;

use Firebase\JWT\ExpiredException;
use GhostAgency\Bundle\StatelessAuthBundle\Encoder\EncoderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class TokenAuthenticator
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class TokenAuthenticator extends AbstractGuardAuthenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const TOKEN_PATTERN = '/^Bearer ([\w-]+\.[\w-]+\.[\w-]*)$/';

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * TokenAuthenticator constructor.
     *
     * @param EncoderInterface $encoder
     */
    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization') && \preg_match(static::TOKEN_PATTERN, $request->headers->get('Authorization'));
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $username = null;
        $matches = [];

        if (\preg_match(self::TOKEN_PATTERN, $request->headers->get('Authorization'), $matches)) {
            try {
                $username = $this->encoder->decode($matches[1])->username;
            } catch (ExpiredException $e) {
                $this->logger->info('[JWT Auth] {message}', ['message' => $e->getMessage()]);
            } catch (\UnexpectedValueException $e) {
                $this->logger->warning(
                    '[JWT Auth] Unable to decode token {token} coming from {ip}: {reason}',
                    [
                        'token'  => $matches[1],
                        'ip'     => $request->getClientIp(),
                        'reason' => $e->getMessage(),
                    ]
                );
            }
        }

        return \compact('username');
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return \key_exists('username', $credentials) && null !== $credentials['username'];
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => 'Bad credentials'], Response::HTTP_FORBIDDEN);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
