<?php namespace Samuelnogueira\ZendExpressiveNewRelic\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Samuelnogueira\ZendExpressiveNewRelic\NewRelicAgentInterface;

class NewRelicMiddleware implements MiddlewareInterface
{
    /** @var NewRelicAgentInterface */
    private $newRelicAgent;

    public function __construct(NewRelicAgentInterface $newRelicAgent)
    {
        $this->newRelicAgent = $newRelicAgent;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->newRelicAgent->startTransaction();
        try {
            $response = $handler->handle($request);
            $this->newRelicAgent->endTransaction();

            return $response;
        } catch (\Throwable $throwable) {
            $this->newRelicAgent->noticeError($throwable->getMessage(), $throwable);
            $this->newRelicAgent->endTransaction();
            throw $throwable;
        }
    }
}