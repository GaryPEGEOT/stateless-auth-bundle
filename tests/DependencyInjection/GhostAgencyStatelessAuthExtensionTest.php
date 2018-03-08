<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\DependencyInjection\GhostAgencyStatelessAuthExtension;

use GhostAgency\Bundle\StatelessAuthBundle\DependencyInjection\GhostAgencyStatelessAuthExtension;
use PHPUnit\Framework\TestCase;

/**
 * Class GhostAgencyStatelessAuthExtensionTest
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class GhostAgencyStatelessAuthExtensionTest extends TestCase
{
    public function testLoad()
    {
        $ext = new GhostAgencyStatelessAuthExtension();
    }
}
