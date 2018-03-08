<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace GhostAgency\Bundle\StatelessAuthBundle\Encoder;

use Firebase\JWT\JWT;

/**
 * JWT encoder & decoder
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class TokenEncoder implements EncoderInterface
{
    const HASH_ALGORITHM = 'HS256';

    /**
     * The JWT hash key.
     *
     * @var string
     */
    private $secret;

    /**
     * Token time to live, in seconds
     *
     * @var int
     */
    private $ttl;

    /**
     * TokenManager constructor.
     *
     * @param string $secret
     * @param int    $ttl
     */
    public function __construct(string $secret, int $ttl = 3600)
    {
        $this->secret = $secret;
        $this->ttl = $ttl;
    }

    /**
     * @param array $payload
     *
     * @return string
     */
    public function encode(array $payload): string
    {
        $now = new \DateTimeImmutable();

        $claims = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->modify("+{$this->ttl} seconds")->getTimestamp(),
        ];

        return JWT::encode(\array_merge($claims, $payload), $this->secret, self::HASH_ALGORITHM);
    }

    /**
     * @param string $token
     *
     * @return object
     *
     * @throws \UnexpectedValueException
     */
    public function decode(string $token)
    {
        return JWT::decode($token, $this->secret, [self::HASH_ALGORITHM]);
    }

}
