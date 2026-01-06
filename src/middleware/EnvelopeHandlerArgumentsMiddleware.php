<?php

namespace App\middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

/**
 * Middleware to pass Envelope as second argument to message handlers
 * 
 * This middleware adds the Envelope to HandlerArgumentsStamp so that handlers
 * can receive it as a second parameter when using #[AsMessageHandler] attribute.
 */
class EnvelopeHandlerArgumentsMiddleware implements MiddlewareInterface
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // Log that middleware is being called (for debugging)
        $this->logger?->debug('EnvelopeHandlerArgumentsMiddleware: Adding Envelope to handler arguments', [
            'message_class' => get_class($envelope->getMessage()),
        ]);

        // Add Envelope to handler arguments so handlers can receive it as second parameter
        // The HandlerArgumentsStamp contains additional arguments that will be passed after the message
        $envelope = $envelope->with(new HandlerArgumentsStamp([$envelope]));
        
        return $stack->next()->handle($envelope, $stack);
    }
}

