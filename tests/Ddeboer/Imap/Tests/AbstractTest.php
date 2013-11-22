<?php
namespace Ddeboer\Imap\Tests;

use Ddeboer\Imap\Exception\MailboxDoesNotExistException;
use Ddeboer\Imap\Mailbox;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Connection;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $connection;

    /**
     * @return Connection
     * @throws \RuntimeException
     */
    protected function getConnection()
    {
        if (null === $this->connection) {
            $server = new Server('imap.gmail.com');

            if (false === \getenv('EMAIL_USERNAME')) {
                throw new \RuntimeException(
                    'Please set environment variable EMAIL_USERNAME before running functional tests'
                );
            }

            if (false === \getenv('EMAIL_PASSWORD')) {
                throw new \RuntimeException(
                    'Please set environment variable EMAIL_PASSWORD before running functional tests'
                );
            }

            $this->connection = $server->authenticate(\getenv('EMAIL_USERNAME'), \getenv('EMAIL_PASSWORD'));
        }

        return $this->connection;
    }

    /**
     * Create a mailbox
     *
     * @param string $name Mailbox name
     *
     * @return Mailbox
     */
    protected function createMailbox($name)
    {
        try {
            $mailbox = $this->getConnection()->getMailbox($name);
            $messages = $mailbox->getMessages();

            foreach ($messages as $message) {
                $message->delete();
            }
            $mailbox->expunge();
            $mailbox->delete();
        } catch (MailboxDoesNotExistException $e) {
            // Ignore mailbox not found
        }

        return $this->getConnection()->createMailbox($name);
    }

    protected function createTestMessage(
        Mailbox $mailbox,
        $subject = 'Don\t panic!',
        $from = 'someone@there.com',
        $to = 'me@here.com',
        $contents = 'Don\'t forget your towel'
    ) {
        $message = "From: $from\r\n"
            . "To: $to\r\n"
            . "Subject: $subject\r\n"
            . "\r\n"
            . "$contents\r\n";

        $mailbox->addMessage($message);
    }
}