<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Tests;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\Exception\AuthenticationFailedException;
use Ddeboer\Imap\Server;

/**
 * @covers \Ddeboer\Imap\Server
 */
final class ServerTest extends AbstractTest
{
    public function testValidConnection()
    {
        $connection = $this->getConnection();

        $check = \imap_check($connection->getResource()->getStream());

        $this->assertInstanceOf(\stdClass::class, $check);
    }

    public function testFailedAuthenticate()
    {
        $server = new Server(\getenv('IMAP_SERVER_NAME'), \getenv('IMAP_SERVER_PORT'), self::IMAP_FLAGS);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessageRegExp('/E_WARNING.+AUTHENTICATIONFAILED/s');

        $server->authenticate(\uniqid('fake_username_'), \uniqid('fake_password_'));
    }

    public function testEmptyPort()
    {
        if ('993' !== (string) \getenv('IMAP_SERVER_PORT')) {
            $this->markTestSkipped('Active IMAP test server must have 993 port for this test');
        }

        $server = new Server(\getenv('IMAP_SERVER_NAME'), '', self::IMAP_FLAGS);

        $this->assertInstanceOf(ConnectionInterface::class, $server->authenticate(\getenv('IMAP_USERNAME'), \getenv('IMAP_PASSWORD')));
    }

    public function test_custom_options()
    {
        $server = new Server(\getenv('IMAP_SERVER_NAME'), \getenv('IMAP_SERVER_PORT'), self::IMAP_FLAGS, [], \OP_HALFOPEN);

        $connection = $server->authenticate(\getenv('IMAP_USERNAME'), \getenv('IMAP_PASSWORD'));

        $check = \imap_check($connection->getResource()->getStream());
        $mailbox = \strtolower($check->Mailbox);

        static::assertContains(\getenv('IMAP_USERNAME'), $mailbox);
        static::assertNotContains('inbox', $mailbox);
        static::assertContains('no_mailbox', $mailbox);
    }
}
